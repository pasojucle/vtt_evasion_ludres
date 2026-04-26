<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\OrderStatusEnum;
use App\Entity\History;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\OrderHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findOrdersByUser(Member $member): array
    {
        $qb = $this->findOrdersByMemberQuery($member);

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOrdersByMemberQuery(Member $member): QueryBuilder
    {
        return $this->createQueryBuilder('oh')
            ->andWhere(
                (new Expr())->eq('oh.member', ':member'),
            )
            ->setParameter('member', $member)
            ->orderBy('oh.createdAt', 'DESC')
        ;
    }

    private function getOrderInProgressByUserQuery(Member $member): QueryBuilder
    {
        return $this->createQueryBuilder('oh')
            ->andWhere(
                (new Expr())->eq('oh.status', ':status'),
                (new Expr())->eq('oh.member', ':member'),
            )
            ->setParameter('status', OrderStatusEnum::IN_PROGRESS)
            ->setParameter('member', $member);
    }

    public function findOneOrderInProgressByUser(Member $member): ?OrderHeader
    {
        try {
            $qb = $this->getOrderInProgressByUserQuery($member);
            return $qb
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findOneOrderNotEmpty(Member $member): ?OrderHeader
    {
        try {
            $qb = $this->getOrderInProgressByUserQuery($member);
            $this->addHavingOrderLineCriteria($qb);
            return $qb
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findOrdersQuery(?OrderStatusEnum $orderStatus = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('oh');

        if ($orderStatus) {
            $qb
                ->andWhere(
                    (new Expr())->eq('oh.status', ':status'),
                )
                ->setParameter('status', $orderStatus)
            ;
        }

        $this->addHavingOrderLineCriteria($qb);

        return $qb
            ->orderBy('oh.id', 'DESC')
        ;
    }

    private function addHavingOrderLineCriteria(QueryBuilder &$qb): QueryBuilder
    {
        return $qb->join('oh.orderLines', 'ol')
            ->having((new Expr())->gt((new Expr())->count('ol'), 0))
            ->groupBy('oh');
    }

    public function findValidedOrCanceled(Member $member): array
    {
        $orderHistories = $this->getEntityManager()->createQueryBuilder()
        ->select('h.entityId')
            ->from(History::class, 'h')
            ->join(OrderHeader::class, 'orderH', 'WITH', (new Expr())->eq('h.entityId', 'orderH.id'))
            ->join(Log::class, 'slog', 'WITH', (new Expr())->andX(
                (new Expr())->eq('h.entityId', 'slog.entityId'),
                (new Expr())->eq('slog.entity', ':entity'),
                (new Expr())->eq('slog.member', ':member')
            ))
            ->andWhere(
                (new Expr())->eq('h.entity', ':entity'),
                (new Expr())->lt('slog.viewAt', 'h.createdAt'),
            );

        return $this->createQueryBuilder('oh')
            ->andWhere(
                (new Expr())->orX(
                    (new Expr())->eq('oh.status', ':valided'),
                    (new Expr())->eq('oh.status', ':canceled'),
                ),
                (new Expr())->eq('oh.member', ':member'),
                (new Expr())->in('oh.id', $orderHistories->getDQL()),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('valided', OrderStatusEnum::VALIDED),
                new Parameter('canceled', OrderStatusEnum::CANCELED),
                new Parameter('member', $member),
                new Parameter('entity', 'OrderHeader'),
            ]))
            ->orderBy('oh.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
