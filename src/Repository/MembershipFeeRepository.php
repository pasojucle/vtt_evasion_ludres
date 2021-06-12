<?php

namespace App\Repository;

use App\Entity\MembershipFee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MembershipFee|null find($id, $lockMode = null, $lockVersion = null)
 * @method MembershipFee|null findOneBy(array $criteria, array $orderBy = null)
 * @method MembershipFee[]    findAll()
 * @method MembershipFee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MembershipFeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipFee::class);
    }

    // /**
    //  * @return MembershipFee[] Returns an array of MembershipFee objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MembershipFee
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
