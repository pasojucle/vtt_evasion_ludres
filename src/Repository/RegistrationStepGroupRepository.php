<?php

namespace App\Repository;

use App\Entity\RegistrationStepGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RegistrationStepGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegistrationStepGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegistrationStepGroup[]    findAll()
 * @method RegistrationStepGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationStepGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistrationStepGroup::class);
    }

    

    /**
    * @return RegistrationStepGroup[] Returns an array of RegistrationStep objects
    */

    public function findAll(): array
    {
        return $this->createQueryBuilder('rsg')
            ->join('rsg.registrationSteps', 'rs')
            ->OrderBy('rsg.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
