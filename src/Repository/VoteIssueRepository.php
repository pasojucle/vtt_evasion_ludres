<?php

namespace App\Repository;

use App\Entity\VoteIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VoteIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoteIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoteIssue[]    findAll()
 * @method VoteIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteIssue::class);
    }

    // /**
    //  * @return VoteIssue[] Returns an array of VoteIssue objects
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
    public function findOneBySomeField($value): ?VoteIssue
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
