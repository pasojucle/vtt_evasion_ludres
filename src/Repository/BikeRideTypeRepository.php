<?php

namespace App\Repository;

use App\Entity\BikeRideType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BikeRideType|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeRideType|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeRideType[]    findAll()
 * @method BikeRideType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeRideTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeRideType::class);
    }

    // /**
    //  * @return BikeRideType[] Returns an array of BikeRideType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BikeRideType
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
