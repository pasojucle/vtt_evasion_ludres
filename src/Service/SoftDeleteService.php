<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Interface\SoftDeletableInterface;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;

class SoftDeleteService
{
    public function __construct(
        private readonly Security $security
    ) {}

    public function softDelete(SoftDeletableInterface $entity): void
    {
        $user = $this->security->getUser();
        
        $entity->setDeletedAt(new DateTimeImmutable());
        
        if ($user instanceof User) {
            $entity->setDeletedBy($user);
        }
    }

    public function restore(SoftDeletableInterface $entity): void
    {
        $entity->setDeletedAt(null)
            ->setDeletedBy(null);
    }
}