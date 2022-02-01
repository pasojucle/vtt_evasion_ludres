<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OrderHeader;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|OrderHeader find($id, $lockMode = null, $lockVersion = null)
 * @method null|OrderHeader findOneBy(array $criteria, array $orderBy = null)
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
                (new Expr())->gte('oh.status', ':status'),
                (new Expr())->neq('oh.status', ':statusCanceled'),
                (new Expr())->eq('oh.user', ':user'),
            )
            ->setParameter('status', OrderHeader::STATUS_ORDERED)
            ->setParameter('statusCanceled', OrderHeader::STATUS_CANCELED)
            ->setParameter('user', $user)
        ;
    }

    public function findOneOrderByUser(User $user): ?OrderHeader
    {
        try {
            return $this->createQueryBuilder('oh')
                ->andWhere(
                    (new Expr())->eq('oh.status', ':status'),
                    (new Expr())->eq('oh.user', ':user'),
                )
                ->setParameter('status', OrderHeader::STATUS_IN_PROGRESS)
                ->setParameter('user', $user)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findOrdersQuery(?array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('oh');

        if (!empty($filters) && !empty($filters['status'])) {
            $qb
                ->andWhere(
                    (new Expr())->eq('oh.status', ':status'),
                )
                ->setParameter('status', $filters['status'])
            ;
        }

        return $qb
            ->orderBy('oh.id', 'DESC')
        ;
    }
}
