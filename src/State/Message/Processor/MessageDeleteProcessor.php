<?php

declare(strict_types=1);

namespace App\State\Message\Processor;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;

class MessageDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Message $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}