<?php

declare(strict_types=1);

namespace App\State\Notification\Processor;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationToggleProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Notification $entity): void
    {
        $entity->setIsDisabled(!$entity->isDisabled());
        
        $this->entityManager->flush();
    }
}