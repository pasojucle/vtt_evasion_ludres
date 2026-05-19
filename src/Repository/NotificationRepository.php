<?php

namespace App\Repository;

use App\Dto\Enum\PublishStatus;
use App\Dto\UserDto;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\Notification;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[] Returns an array of FlashInfo objects
     */
    public function findAllDesc(): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Notification[] Returns an array of FlashInfo objects
     */
    public function findByUser(Member $member, int $age): array
    {
        $viewed = $this->getEntityManager()->createQueryBuilder()
        ->select('log.entityId')
        ->from(Log::class, 'log')
        ->andWhere(
            (new Expr())->eq('log.member', ':member'),
            (new Expr())->eq('log.entity', ':entityName')
        );

        $today = new DateTime();
        return $this->createQueryBuilder('n')
            ->andWhere(
                (new Expr())->lte('n.startAt', ':today'),
                (new Expr())->gte('n.endAt', ':today'),
                (new Expr())->eq('n.isDisabled', ':disabled'),
                (new Expr())->eq('n.public', ':public'),
                (new Expr())->orX(
                    (new Expr())->lte('n.minAge', ':age'),
                    (new Expr())->isNull('n.minAge')
                ),
                (new Expr())->orX(
                    (new Expr())->isNull('n.maxAge'),
                    (new Expr())->gte('n.maxAge', ':age')
                ),
                (new Expr())->notIn('n.id', $viewed->getDQL())
            )
            ->setParameters(new ArrayCollection([
                new Parameter('today', $today->format('Y-m-d H:i:s')),
                new Parameter('disabled', false),
                new Parameter('public', false),
                new Parameter('age', $age),
                new Parameter('member', $member),
                new Parameter('entityName', 'Notification')
            ]))
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Notification[] Returns an array of FlashInfo objects
     */
    public function findPublic(): array
    {
        $today = new DateTime();
        return $this->createQueryBuilder('n')
            ->andWhere(
                (new Expr())->lte('n.startAt', ':today'),
                (new Expr())->gte('n.endAt', ':today'),
                (new Expr())->eq('n.isDisabled', ':disabled'),
                (new Expr())->eq('n.public', ':public')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('today', $today->format('Y-m-d H:i:s')),
                new Parameter('disabled', 0),
                new Parameter('public', 1),
            ]))
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNotificationQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('n');
    }

    public function filterDisabled(QueryBuilder $qb, PublishStatus $state): void
    {
        $qb
        ->andWhere(
            $qb->expr()->eq('n.isDisabled', ':state')
        )
        ->setParameter('state', PublishStatus::DISABLED === $state);
    }

    public function filterIsPublic(QueryBuilder $qb, bool $isPublic): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('n.public', ':isPublic')
            )
            ->setParameter('isPublic', $isPublic);
    }


    public function filterHasAge(QueryBuilder $qb): void
    {
        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNotNull('n.minAge'),
                    $qb->expr()->isNotNull('n.maxAge'),
                )
            );
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('n.startAt', $direction);
    }
}
