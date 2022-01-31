<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class IdentityService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setAddress(User $user)
    {
        foreach ($user->getIdentities() as $identity) {
            if (null !== $identity->getKinShip()) {
                $addressKinShip = $identity->getAddress();
                if (! $identity->hasAddress() && null !== $addressKinShip) {
                    $identity->setAddress(null);
                    $this->entityManager->remove($addressKinShip);
                }
            }
        }
    }
}
