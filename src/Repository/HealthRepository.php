<?php

namespace App\Repository;

use App\Entity\Health;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Health|null find($id, $lockMode = null, $lockVersion = null)
 * @method Health|null findOneBy(array $criteria, array $orderBy = null)
 * @method Health[]    findAll()
 * @method Health[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HealthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Health::class);
    }

    // /**
    //  * @return Health[] Returns an array of Health objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Health
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
