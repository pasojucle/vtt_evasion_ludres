<?php

declare(strict_types=1);

namespace App\EventListeners;

use App\Entity\SecondHand;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::postLoad)]
class CurrencyListener
{
    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof SecondHand) {
            $this->convertFloatToInt($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof SecondHand) {
            $this->convertFloatToInt($entity);
        }
    }

    public function postLoad(PostLoadEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof SecondHand) {
            $this->convertIntToFloat($entity);
        }
    }

    private function convertFloatToInt(SecondHand $entity): void
    {
        $amount = $entity->getPrice();
        $entity->setPrice((int) $amount * 100);
    }

    private function convertIntToFloat(SecondHand $entity): void
    {
        $amount = $entity->getPrice();
        $entity->setPrice($amount / 100);
    }
}
