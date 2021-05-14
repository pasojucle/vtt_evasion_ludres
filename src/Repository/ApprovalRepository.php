<?php

namespace App\Repository;

use App\Entity\Approval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Approval|null find($id, $lockMode = null, $lockVersion = null)
 * @method Approval|null findOneBy(array $criteria, array $orderBy = null)
 * @method Approval[]    findAll()
 * @method Approval[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprovalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Approval::class);
    }

    // /**
    //  * @return Approval[] Returns an array of Approval objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Approval
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
