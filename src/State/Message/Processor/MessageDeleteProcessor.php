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

    public function process(Message $entity): int
    {
        $section = $entity->getSection();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return $section->getId();
    }
}