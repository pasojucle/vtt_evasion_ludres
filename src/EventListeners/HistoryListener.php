<?php

declare(strict_types=1);

namespace App\EventListeners;

use App\Entity\Address;
use App\Entity\Health;
use App\Entity\History;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\User;
use App\Service\SeasonService;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use ReflectionClass;

#[AsDoctrineListener(event: Events::postUpdate)]
class HistoryListener
{
    private EntityManagerInterface $objectManager;
    
    public function __construct(
        private readonly SeasonService $seasonService,
    ) {
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $entity = $event->getObject();
        $reflexionClass = new ReflectionClass($entity);
        $className = $reflexionClass->getShortName();
        $this->objectManager = $event->getObjectManager();
        $unitOfWork = $this->objectManager->getUnitOfWork();
        $changeSet = $unitOfWork->getEntityChangeSet($entity);
        $className = $reflexionClass->getShortName();

        if ($entity instanceof Address || $entity instanceof Health || $entity instanceof Identity || $entity instanceof Licence) {
            $this->addRegistrationHistory($reflexionClass, $className, $entity, $changeSet);
        }
        if ($entity instanceof Survey) {
            $this->addSurveyHistory($className, $entity, $changeSet);
        }
        if ($entity instanceof SurveyIssue) {
            $this->addSurveyIssueHistory($className, $entity, $changeSet);
        }
    }

    private function addRegistrationHistory(ReflectionClass $reflexionClass, string $className, Address|Health|Identity|Licence $entity, array $changeSet): void
    {
        $this->checkPhone($changeSet);
        if (empty($changeSet)) {
            return;
        }
        $user = ($reflexionClass->hasMethod('getUser'))
        ? $entity->getUser()
        : $entity->getIdentities()->first()->getUser();
        if (1 < $user->getLicences()->count()) {
            $seasonPeriod = $this->seasonService->getCurrentSeasonPeriod();
            $history = $this->objectManager->getRepository(History::class)->findOneByRegistrationEntity($user, $className, $entity->getId(), $seasonPeriod['startAt']);
            $this->addHistory($history, $user, $className, $entity, $changeSet);
        }
    }

    private function checkPhone(array &$changeSet): void
    {
        $keys = ['phone', 'mobile', 'emergencyPhone'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $changeSet)) {
                list($old, $new) = $changeSet[$key];
                if ($new && $old === str_replace(' ', '', $new)) {
                    unset($changeSet[$key]);
                }
            }
        }
    }

    private function addSurveyHistory(string $className, Survey $entity, array $changeSet): void
    {
        $history = $this->objectManager->getRepository(History::class)->findOneByEntity($className, $entity->getId());
        $this->addHistory($history, null, $className, $entity, $changeSet);
    }

    private function addSurveyIssueHistory(string $className, SurveyIssue $entity, array $changeSet): void
    {
        $history = $this->objectManager->getRepository(History::class)->findOneByEntity($className, $entity->getId());
        $this->addHistory($history, null, $className, $entity, $changeSet);
    }

    private function addHistory(?History $history, ?User $user, string $className, Address|Health|Identity|Licence|Survey|SurveyIssue $entity, array $changeSet): void
    {
        if (null === $history) {
            $history = new History();
            $this->objectManager->persist($history);
        }

        $changes = $history->getValue();
        $changes = array_merge($changes, $changeSet);
        
        $history->setUser($user)
            ->setCreatedAt(new DateTimeImmutable())
            ->setEntity($className)
            ->setEntityId($entity->getId())
            ->setValue($changes)
            ;

        $this->objectManager->flush();
    }
}
