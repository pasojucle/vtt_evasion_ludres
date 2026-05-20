<?php

declare(strict_types=1);

namespace App\State\BoardRole\Processor;

use App\Entity\BoardRole;
use Doctrine\ORM\EntityManagerInterface;

class BoardRoleDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(BoardRole $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}