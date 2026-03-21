<?php

namespace App\Repository;

use App\Entity\Log;
use App\Entity\Member;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }


    public function findOneByRouteAndUser(string $route, Member $member): ?Log
    {
        try {
            return $this->createQueryBuilder('l')
                ->andWhere(
                    (new Expr())->eq('l.member', ':member'),
                    (new Expr())->eq('l.route', ':route'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('member', $member),
                    new Parameter('route', $route),
                ]))
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findOneByEntityAndUser(string $className, int $entityId, Member $member): ?Log
    {
        try {
            return $this->createQueryBuilder('l')
                ->andWhere(
                    (new Expr())->eq('l.user', ':member'),
                    (new Expr())->eq('l.entity', ':className'),
                    (new Expr())->eq('l.entityId', ':entityId'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('member', $member),
                    new Parameter('className', $className),
                    new Parameter('entityId', $entityId),
                ]))
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findOutOfPeriod(DateTimeImmutable $deadline): array
    {
        return $this->createQueryBuilder('sh')
            ->andWhere(
                (new Expr())->lt('sh.viewAt', ':deadline'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('deadline', $deadline),
            ]))
            ->getQuery()
            ->getResult();
    }

    private function findEntityViewedIds(Member $member, string $entityName): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.entityId')
            ->andWhere(
                (new Expr())->eq('l.member', ':member'),
                (new Expr())->eq('l.entity', ':entityName')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('member', $member),
                new Parameter('entityName', $entityName)
            ]))
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findSlideShowimageViewedIds(Member $member): array
    {
        return $this->findEntityViewedIds($member, 'SlideshowImage');
    }

    public function findSummaryViewedIds(Member $member): array
    {
        return $this->findEntityViewedIds($member, 'Summary');
    }

    public function findSecondHandViewedIds(Member $member): array
    {
        return $this->findEntityViewedIds($member, 'SecondHand');
    }

    public function findLatestView(Member $member, string $entity)
    {
        return $this->createQueryBuilder('l')
            ->select((new Expr())->max('l.viewAt'))
            ->andWhere(
                (new Expr())->eq('l.user', ':user'),
                (new Expr())->eq('l.entity', ':entityName'),
                (new Expr())->lt('l.viewAt', ':today'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('member', $member),
                new Parameter('entityName', $entity),
                new Parameter('today', (new DateTimeImmutable())),
            ]))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
