<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Interface\DisableableInterface;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;

class DisableService
{
    public function __construct(
        private readonly Security $security
    ) {}

    public function toggle(DisableableInterface $entity): void
    {
        if ($entity->isDisabled()) {
            $entity->setDisabledAt(null)
                ->setDisabledBy(null);
            return;
        }

        $user = $this->security->getUser();
        
        $entity->setDisabledAt(new DateTimeImmutable());
        
        if ($user instanceof User) {
            $entity->setDisabledBy($user);
        }
    }
}