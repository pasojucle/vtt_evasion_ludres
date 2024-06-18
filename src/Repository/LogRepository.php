<?php

namespace App\Repository;

use App\Entity\Log;
use App\Entity\User;
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


    public function findOneByRouteAndUser(string $route, User $user): ?Log
    {
        try {
            return $this->createQueryBuilder('l')
                ->andWhere(
                    (new Expr())->eq('l.user', ':user'),
                    (new Expr())->eq('l.route', ':route'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('user', $user),
                    new Parameter('route', $route),
                ]))
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findOneByEntityAndUser(string $className, int $entityId, User $user): ?Log
    {
        try {
            return $this->createQueryBuilder('l')
                ->andWhere(
                    (new Expr())->eq('l.user', ':user'),
                    (new Expr())->eq('l.entity', ':className'),
                    (new Expr())->eq('l.entityId', ':entityId'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('user', $user),
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

    private function findEntityViewedIds(User $user, string $entityName): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.entityId')
            ->andWhere(
                (new Expr())->eq('l.user', ':user'),
                (new Expr())->eq('l.entity', ':entityName')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('entityName', $entityName)
            ]))
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findSlideShowimageViewedIds(User $user): array
    {
        return $this->findEntityViewedIds($user, 'SlideshowImage');
    }

    public function findSummaryViewedIds(User $user): array
    {
        return $this->findEntityViewedIds($user, 'Summary');
    }

    public function findSecondHandViewedIds(User $user): array
    {
        return $this->findEntityViewedIds($user, 'SecondHand');
    }
}
