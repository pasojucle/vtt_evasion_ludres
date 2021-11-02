<?php

namespace App\Repository;

use App\Entity\LogError;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogError|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogError|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogError[]    findAll()
 * @method LogError[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogErrorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogError::class);
    }

    // /**
    //  * @return LogError[] Returns an array of LogError objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LogError
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
