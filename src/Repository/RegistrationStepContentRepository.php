<?php

namespace App\Repository;

use App\Entity\RegistrationStepContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RegistrationStepContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegistrationStepContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegistrationStepContent[]    findAll()
 * @method RegistrationStepContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationStepContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistrationStepContent::class);
    }

    // /**
    //  * @return RegistrationStepContent[] Returns an array of RegistrationStepContent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RegistrationStepContent
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
