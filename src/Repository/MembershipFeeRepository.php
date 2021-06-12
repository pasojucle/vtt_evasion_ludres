<?php

namespace App\Repository;

use Doctrine\ORM\Query\Expr;
use App\Entity\MembershipFee;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;

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
}
