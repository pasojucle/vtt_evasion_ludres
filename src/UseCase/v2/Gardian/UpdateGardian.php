<?php

declare(strict_types=1);

namespace App\UseCase\v2\Gardian;

use App\Entity\MemberGardian;
use Doctrine\ORM\EntityManagerInterface;

class UpdateGardian
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(?MemberGardian $gardian) {
        
        $gardianIdentity = $gardian->getIdentity();
        $gardianAddress = $gardianIdentity->getAddress();
        if (!$gardianIdentity->hasAddress() && null !== $gardianAddress) {
            $gardianIdentity->setAddress(null);
            $this->entityManager->remove($gardianAddress);
        }

        $this->entityManager->flush();
    }
}
