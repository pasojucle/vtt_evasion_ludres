<?php

declare(strict_types=1);

namespace App\EventListeners;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
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
        $this->updateDtatime($event);
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $this->updateDtatime($event);
    }

    private function updateDtatime(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        $entity = $event->getObject();
        $reflexionClass = new ReflectionClass($entity);
        if ($reflexionClass->hasMethod('setUpdateAt')) {
            $entity->setUpdateAt(new DateTimeImmutable());
        }
    }
}
