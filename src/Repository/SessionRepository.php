<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Cluster;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\LevelType;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Session;
use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function findOneByUserAndClusters(Member $member, Collection $clusers): ?Session
    {
        try {
            return $this->createQueryBuilder('s')
            ->andWhere(
                (new Expr())->in('s.cluster', ':clusers'),
                (new Expr())->eq('s.user', ':member'),
            )
            ->setParameter('clusers', $clusers)
            ->setParameter('member', $member)
            ->getQuery()
            ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findOneByUserAndCluster(Member $member, Cluster $cluser): ?Session
    {
        try {
            return $this->createQueryBuilder('s')
            ->andWhere(
                (new Expr())->eq('s.cluster', ':cluser'),
                (new Expr())->eq('s.user', ':member'),
            )
            ->setParameter('cluser', $cluser)
            ->setParameter('member', $member)
            ->getQuery()
            ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findOneByUserAndBikeRide(User $user, BikeRide $bikeRide): ?Session
    {
        try {
            return $this->createQueryBuilder('s')
            ->join('s.cluster', 'c')
            ->andWhere(
                (new Expr())->eq('c.bikeRide', ':bikeRide'),
                (new Expr())->eq('s.user', ':user'),
            )
            ->setParameter('bikeRide', $bikeRide)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findByBikeRide(BikeRide $bikeRide): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.cluster', 'c')
            ->andWhere(
                (new Expr())->eq('c.bikeRide', ':bikeRide'),
            )
            ->setParameter('bikeRide', $bikeRide)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByBikeRideId(int $bikeRideId): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.cluster', 'c')
            ->leftJoin('c.bikeRide', 'b')
            ->andWhere(
                (new Expr())->eq('b.id', ':bikeRideId'),
                (new Expr())->orX(
                    (new Expr())->eq('s.availability', ':registered'),
                    (new Expr())->eq('s.availability', ':none'),
                )
            )
            ->setParameters(new ArrayCollection([
                new Parameter('bikeRideId', $bikeRideId),
                new Parameter('registered', AvailabilityEnum::REGISTERED),
                new Parameter('none', AvailabilityEnum::NONE),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }


    public function findFramersByBikeRide(int $bikeRideId): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.cluster', 'c')
            ->leftJoin('c.bikeRide', 'b')
            ->join('s.user', 'u')
            ->join('u.level', 'l')
            ->andWhere(
                (new Expr())->eq('b.id', ':bikeRideId'),
                (new Expr())->eq('l.type', ':levelType'),
            )
            ->setParameter('bikeRideId', $bikeRideId)
            ->setParameter('levelType', LevelType::FRAME)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByUserAndFilters(User $user, array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->leftJoin('s.cluster', 'c')
            ->leftJoin('c.bikeRide', 'br')
            ->andWhere(
                $qb->expr()->eq('s.user', ':user')
            )
            ->setParameter('user', $user)
            ->orderBy('br.startAt')
            ;
        if (isset($filters['startAt']) && isset($filters['endAt'])) {
            $qb
                ->andWhere(
                    $qb->expr()->between('br.startAt', ':startAt', ':endAt')
                )
                ->setParameter('startAt', $filters['startAt'])
                ->setParameter('endAt', $filters['endAt']);
        }
        if (isset($filters['bikeRideType'])) {
            $qb
                ->andWhere(
                    $qb->expr()->eq('br.bikeRideType', ':bikeRideType')
                )
                ->setParameter('bikeRideType', $filters['bikeRideType']);
        }

        return $qb;
    }

    public function findByFilters(array $filters): array
    {
        $parameters = [];
        $andX = (new Expr())->andX();
        if (isset($filters['startAt']) && isset($filters['endAt'])) {
            $andX->add((new Expr())->between('br.startAt', ':startAt', ':endAt'));
            $parameters[] = new Parameter('startAt', $filters['startAt']);
            $parameters[] = new Parameter('endAt', $filters['endAt']);
        }

        if (isset($filters['bikeRideType'])) {
            $andX->add((new Expr())->eq('br.bikeRideType', ':bikeRideType'));
            $parameters[] = new Parameter('bikeRideType', $filters['bikeRideType']);
        }

        if (isset($filters['levels'])) {
            $this->addCriteriaByLevel($andX, $parameters, $filters['levels']);
        }

        if (isset($filters['practice'])) {
            $andX->add((new Expr())->eq('s.practice', ':practice'));
            $parameters[] = new Parameter('practice', $filters['practice']);
        }

        return $this->createQueryBuilder('s')
            ->leftJoin('s.cluster', 'c')
            ->leftJoin('c.bikeRide', 'br')
            ->leftJoin('s.user', 'u')
            ->leftJoin('u.level', 'l')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
            ;
    }

    private function addCriteriaByLevel(Andx &$andX, array &$parameters, array $filterLevels): void
    {
        $types = [];
        $levels = [];
        $isBoardmember = false;

        foreach ($filterLevels as $level) {
            match ($level) {
                Level::TYPE_ALL_MEMBER => $types[] = LevelType::SCHOOL,
                Level::TYPE_ALL_FRAME => $types[] = LevelType::FRAME,
                Level::TYPE_BOARD_MEMBER => $isBoardmember = true,
                default => $levels[] = $level,
            };
        }
 
        $orX = (new Expr())->orX();
        if (!empty($levels)) {
            $orX->add((new Expr())->in('u.level', ':levels'));
            $parameters[] = new Parameter('levels', $levels);
        }

        if (!empty($types)) {
            $orX->add((new Expr())->in('l.type', ':types'));
            $parameters[] = new Parameter('types', $types);
        }

        if ($isBoardmember) {
            $orX->add((new Expr())->isNotNull('u.boardRole'));
        }

        if (0 < $orX->count()) {
            $andX->add($orX);
        }
    }

    public function findOfTheDayByUser(Member $member): ?Session
    {
        try {
            $today = new DateTimeImmutable();
            return $this->createQueryBuilder('s')
                ->join('s.cluster', 'c')
                ->join('c.bikeRide', 'br')
                ->andWhere(
                    (new Expr())->eq('s.availability', ':availability'),
                    (new Expr())->eq('s.user', 'member'),
                    (new Expr())->between('br.startAt', ':start', ':end'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('availability', Session::AVAILABILITY_REGISTERED),
                    new Parameter('member', $member),
                    new Parameter('start', $today->setTime(0, 0, 0)),
                    new Parameter('end', $today->setTime(18, 0, 0)),
                ]))
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    /**
     * @param bool $isSchool
     * @param DateTimeImmutable $startAt
     * @param DateTimeImmutable $endAt
     * @return array<int, array{total: int, date: \DateTimeInterface}>
     */
    public function findParticipation(bool $isSchool, DateTimeImmutable $startAt, DateTimeImmutable $endAt): array
    {
        return $this->createQueryBuilder('s')
            ->select(sprintf('%s as total', (new Expr())->count('s.isPresent')), 'br.startAt as date')
            ->join('s.cluster', 'c')
            ->join('c.bikeRide', 'br')
            ->join('br.bikeRideType', 'brt')
            ->andWhere(
                (new Expr())->eq('s.isPresent', ':isPresent'),
                (new Expr())->eq('brt.needFramers', ':needFramers'),
                (new Expr())->between('br.startAt', ':startAt', ':endAt')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('isPresent', true),
                new Parameter('needFramers', $isSchool),
                new Parameter('startAt', $startAt),
                new Parameter('endAt', $endAt)
            ]))
            ->groupBy('c.bikeRide')
            ->orderBy('br.startAt')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAvailableByUser(Member $member): array
    {
        $today = new DateTimeImmutable();
        return $this->createQueryBuilder('s')
            ->join('s.cluster', 'c')
            ->join('c.bikeRide', 'br')
            ->andWhere(
                (new Expr())->eq('s.user', ':member'),
                (new Expr())->gte('br.startAt', ':start'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('member', $member),
                new Parameter('start', $today->setTime(0, 0, 0)),
            ]))
            ->getQuery()
            ->getResult();
    }

    public function findFramerAvailability(): array
    {
        $bikeRides = $this->getEntityManager()->createQueryBuilder()
            ->select('bikeRide.id')
            ->from(BikeRide::class, 'bikeRide')
            ->join('bikeRide.bikeRideType', 'brt')
            ->andWhere(
                (new Expr())->gte('bikeRide.startAt', ':start'),
                (new Expr())->lte('bikeRide.startAt', ':end'),
                (new Expr())->eq('bikeRide.deleted', ':deleted'),
                (new Expr())->gt('brt.registration', ':registration'),
                (new Expr())->eq('brt.needFramers', ':needFramers'),
            )
        ;

        return $this->createQueryBuilder('s')
            ->join('s.cluster', 'c')
            ->join('c.bikeRide', 'br')
            ->andWhere(
                (new Expr())->eq('s.availability', ':avaylability'),
                (new Expr())->in('br.id', $bikeRides->getDql()),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('start', (new DateTimeImmutable())->add((new DateInterval('P1D')))->setTime(0, 0, 0)),
                new Parameter('end', (new DateTimeImmutable())->add((new DateInterval('P1D')))->setTime(23, 59, 59)),
                new Parameter('registration', RegistrationEnum::NONE),
                new Parameter('deleted', false),
                new Parameter('needFramers', true),
                new Parameter('avaylability', AvailabilityEnum::AVAILABLE),
            ]))
            ->getQuery()
            ->getResult();
    }

    // public function findParticipationByUser(User $user): int
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select((new Expr())->count('s.isPresent'))
    //         ->andWhere(
    //             (new Expr())->eq('s.user', ':user')
    //         )
    //         ->setParameter('user', $user)
    //         ->getQuery()
    //         ->getSingleScalarResult()
    //     ;
    // }

    public function findParticipationByUser(Member $member): int
    {
        return $this->createQueryBuilder('s')
            ->select((new Expr())->count('s.isPresent'))
            ->andWhere(
                (new Expr())->eq('s.user', ':member'),
                (new Expr())->eq('s.isPresent', ':isPresent')
            )
            ->setParameter('member', $member)
            ->setParameter('isPresent', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findTotalByActivityIds(array $activityIds): array
    {
        return $this->createQueryBuilder('s')
            ->select('b.id')
            ->addSelect(sprintf('%s as count', (new Expr())->count('s.id')))
            ->addSelect('SUM(CASE WHEN s.isPresent = true THEN 1 ELSE 0 END) as present')
            ->join('s.cluster', 'c')
            ->join('c.bikeRide', 'b')
            ->andWhere(
                (new Expr())->in('b.id', ':ids'),
            )
            ->setParameter('ids', $activityIds)
            ->groupBy('b.id')
            ->getQuery()
            ->getArrayResult();
    }

    public function getSessionQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('se')
            ->join('se.cluster', 'cl')->addSelect('cl')
            ->join('cl.bikeRide', 'br')->addSelect('br');
    }

    public function getCountSessionQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('se')
            ->select(
                'COUNT(se.isPresent) as total',
                "DATE_FORMAT(br.startAt, '%Y-%m') as month"
            )
            ->join('se.cluster', 'cl')
            ->join('cl.bikeRide', 'br')
            ->groupBy('month')
            ->orderBy('month', 'ASC');
    }

    public function filterMember(QueryBuilder $qb, Member $member): void
    {
        $qb->andWhere(
            $qb->expr()->eq('se.member', ':member')
        )
        ->setParameter('member', $member);
    }

    public function filterType(QueryBuilder $qb, BikeRideType $bikeRideType): void
    {
        $qb->andWhere(
            $qb->expr()->eq('br.bikeRideType', ':bikeRideType')
        )
        ->setParameter('bikeRideType', $bikeRideType);
    }

    public function filterparticipated(QueryBuilder $qb): void
    {
        $qb->andWhere(
            $qb->expr()->eq('se.isPresent', ':isPresent')
        )
        ->setParameter('isPresent', true);
    }

    public function filterUser(QueryBuilder $qb, User $user): void
    {
        $qb->andWhere(
            $qb->expr()->eq('se.user', ':user')
        )
        ->setParameter('user', $user);
    }

    public function filterPeriod(QueryBuilder $qb, DateTimeImmutable $startAt, DateTimeImmutable $endAt): void
    {
        $qb
            ->andWhere(
                $qb->expr()->between('br.startAt', ':startAt', ':endAt')
            )
            ->setParameter('startAt', $startAt)
            ->setParameter('endAt', $endAt);
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('br.startAt', $direction);
    }
}
