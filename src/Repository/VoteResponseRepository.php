<?php

namespace App\Repository;

use App\Entity\VoteResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VoteResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoteResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoteResponse[]    findAll()
 * @method VoteResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteResponse::class);
    }

    // /**
    //  * @return VoteResponse[] Returns an array of VoteResponse objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VoteResponse
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
