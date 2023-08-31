<?php

declare(strict_types=1);

namespace App\EventListeners;

use App\Entity\Approval;
use App\Entity\RegistrationChange;
use App\Entity\User;
use App\Service\SeasonService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use ReflectionClass;
use function Symfony\Component\String\u;

#[AsDoctrineListener(event: Events::postUpdate)]
class RegistrationChangeListener
{
    public function __construct(private SeasonService $seasonService)
    {
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $entity = $event->getObject();
        $reflexionClass = new ReflectionClass($entity);
        $className = $reflexionClass->getShortName();
        if (1 === preg_match('#Address|Health|Identity|Licence#', $className)) {
            /** @var EntityManager $objectManager*/
            $objectManager = $event->getObjectManager();
            $unitOfWork = $objectManager->getUnitOfWork();
            $changeSet = $unitOfWork->getEntityChangeSet($entity);

            $user = ($reflexionClass->hasMethod('getUser'))
                 ? $entity->getUser()
                 : $entity->getIdentities()->first()->getUser();

            $season = $this->seasonService->getCurrentSeason();
            $className = $reflexionClass->getShortName();
            if (1 < $user->getLicences()->count()) {
                $registrationChange = $objectManager->getRepository(RegistrationChange::class)->findOneByEntity($user, $className, $entity->getId(), $season);
                if (null === $registrationChange) {
                    $registrationChange = new RegistrationChange();
                    $objectManager->persist($registrationChange);
                }


                $changes = $registrationChange->getValue();
                $changes = array_merge($changes, $changeSet);
                
                $registrationChange->setUser($user)
                    ->setSeason($season)
                    ->setEntity($reflexionClass->getShortName())
                    ->setEntityId($entity->getId())
                    ->setValue($changes)
                    ;
                
                $objectManager->flush();
            }
        }
    }
}
