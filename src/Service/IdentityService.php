<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Error;

class IdentityService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function setAddress(User $user): void
    {
        foreach ($user->getIdentities() as $identity) {
            if (null !== $identity->getKinShip()) {
                $addressKinShip = $identity->getAddress();
                if (!$identity->hasAddress() && null !== $addressKinShip) {
                    $identity->setAddress(null);
                    $this->entityManager->remove($addressKinShip);
                }
            }
        }
    }

    public function getMainContact(User $user): Identity
    {
        $licence = $user->getLastLicence();
        return (Licence::CATEGORY_MINOR === $licence?->getCategory())
        ? $user->getKinshipIdentity()
        : $user->getMemberIdentity();
    }
}
