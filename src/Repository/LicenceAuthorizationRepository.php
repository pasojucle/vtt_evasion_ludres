<?php

namespace App\Repository;

use App\Entity\LicenceAuthorization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LicenceAuthorization>
 */
class LicenceAuthorizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenceAuthorization::class);
    }
}
