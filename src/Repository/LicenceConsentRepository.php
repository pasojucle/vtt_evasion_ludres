<?php

namespace App\Repository;

use App\Entity\LicenceConsent;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<LicenceConsent>
 */
class LicenceConsentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenceConsent::class);
    }

}
