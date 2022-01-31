<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MembershipFee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|MembershipFee find($id, $lockMode = null, $lockVersion = null)
 * @method null|MembershipFee findOneBy(array $criteria, array $orderBy = null)
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
