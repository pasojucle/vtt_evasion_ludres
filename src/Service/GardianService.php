<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;

class GardianService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function setAddress(Member $member): void
    {
        foreach ($member->getMemberGardians() as $gardian) {
            $gardianIdentity = $gardian->getIdentity();
            $gardianAddress = $gardianIdentity->getAddress();
            if (!$gardianIdentity->hasAddress() && null !== $gardianAddress) {
                $gardianIdentity->setAddress(null);
                $this->entityManager->remove($gardianAddress);
            }
        }
    }
}
