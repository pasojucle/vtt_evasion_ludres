<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\OrderHeader;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method OrderHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderHeader[]    findAll()
 * @method OrderHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderHeader::class);
    }

    public function findOrdersByUser(User $user): array
    {
        $qb = $this->findOrdersByUserQuery($user);
        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOrdersByUserQuery(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('oh')
        ->andWhere(
            (new Expr)->gte('oh.status', ':status'),
            (new Expr)->eq('oh.user', ':user'),
            (new Expr)->eq('oh.isDisabled', ':disabled'),
        )
        ->setParameter('status', OrderHeader::STATUS_ORDERED)
        ->setParameter('user', $user)
        ->setParameter('disabled', false)
        ;
    }

    public function findOneOrderByUser(User $user): ?OrderHeader
    {
        try {
            return $this->createQueryBuilder('oh')
            ->andWhere(
                (new Expr)->eq('oh.status', ':status'),
                (new Expr)->eq('oh.user', ':user'),
            )
            ->setParameter('status', OrderHeader::STATUS_IN_PROGRESS)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
            ;
        } catch(NonUniqueResultException $e) {
            return null;
        }
    }


    public function findOrdersQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('oh')
        ->andWhere(
            (new Expr)->gte('oh.status', ':status'),
            (new Expr)->eq('oh.isDisabled', ':disabled'),
        )
        ->setParameter('status', OrderHeader::STATUS_ORDERED)
        ->setParameter('disabled', false)
            ->orderBy('oh.id', 'DESC')
        ;
    }
}
