<?php

declare(strict_types=1);

namespace App\State\Member\Processor;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;

class MemberDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(Member $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}