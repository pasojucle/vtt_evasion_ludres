<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use App\Repository\IdentityRepository;
use Doctrine\ORM\EntityManagerInterface;

class IdentityService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IdentityRepository $identityRepository,
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
        ? $this->identityRepository->findOneKinshipByUser($user)
        : $this->identityRepository->findOneMemberByUser($user);
    }

    public function getMember(User $user): Identity
    {
        return $this->identityRepository->findOneMemberByUser($user);
    }
}
