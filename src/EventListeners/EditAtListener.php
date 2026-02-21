<?php

declare(strict_types=1);

namespace App\EventListeners;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use ReflectionClass;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class EditAtListener
{
    public function prePersist(PrePersistEventArgs $event): void
    {
        $this->updateDatetime($event);
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $this->updateDatetime($event);
    }

    private function updateDatetime(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        $entity = $event->getObject();
        $reflexionClass = new ReflectionClass($entity);
        if ($reflexionClass->hasMethod('setUpdateAt')) {
            if ($event instanceof PreUpdateEventArgs) {
                /** @var EntityManagerInterface $objectManager */
                $objectManager = $event->getObjectManager();
                $unitOfWork = $objectManager->getUnitOfWork();
                $changeSet = $unitOfWork->getEntityChangeSet($entity);
                if (array_key_exists('orderBy', $changeSet)) {
                    return;
                }
            }
            $entity->setUpdateAt(new DateTimeImmutable());
        }
    }
}
