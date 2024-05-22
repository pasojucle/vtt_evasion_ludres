<?php

declare(strict_types=1);

namespace App\EventListeners;

use App\Entity\Address;
use App\Entity\Health;
use App\Entity\History;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\Respondent;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\User;
use App\Service\SeasonService;
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
            $this->addSurveyHistory($reflexionClass, $className, $entity, $changeSet);
        }
        if ($entity instanceof SurveyIssue) {
            $this->addSurveyIssueHistory($reflexionClass, $className, $entity, $changeSet);
        }
    }

    private function addRegistrationHistory(ReflectionClass $reflexionClass, string $className, Address|Health|Identity|Licence $entity, array $changeSet): void
    {
        $user = ($reflexionClass->hasMethod('getUser'))
        ? $entity->getUser()
        : $entity->getIdentities()->first()->getUser();
        if (1 < $user->getLicences()->count()) {
            $season = $this->seasonService->getCurrentSeason();
            $history = $this->objectManager->getRepository(History::class)->findOneByRegistrationEntity($user, $className, $entity->getId(), $season);
            $this->addHistory($history, $user, $className, $entity, $changeSet, $season);
        }
    }

    private function addSurveyHistory(ReflectionClass $reflexionClass, string $className, Survey $entity, array $changeSet): void
    {
        $history = $this->objectManager->getRepository(History::class)->findOneByEntity($className, $entity->getId());
        $this->respondentSurveyChanged($entity);
        $this->addHistory($history, null, $className, $entity, $changeSet);
    }

    private function addSurveyIssueHistory(ReflectionClass $reflexionClass, string $className, SurveyIssue $entity, array $changeSet): void
    {
        $history = $this->objectManager->getRepository(History::class)->findOneByEntity($className, $entity->getId());
        $this->respondentSurveyChanged($entity->getSurvey());
        $this->addHistory($history, null, $className, $entity, $changeSet);
    }

    private function respondentSurveyChanged(Survey $survey): void
    {
        /** @var Respondent $respondent */
        foreach ($survey->getRespondents() as $respondent) {
            $respondent->setSurveyChanged(true);
        }
    }

    private function addHistory(?History $history, ?User $user, string $className, Address|Health|Identity|Licence|Survey|SurveyIssue $entity, array $changeSet, ?int $season = null): void
    {
        if (null === $history) {
            $history = new History();
            $this->objectManager->persist($history);
        }

        $changes = $history->getValue();
        $changes = array_merge($changes, $changeSet);
        
        $history->setUser($user)
            ->setSeason($season)
            ->setEntity($className)
            ->setEntityId($entity->getId())
            ->setValue($changes)
            ;

        $this->objectManager->flush();
    }
}
