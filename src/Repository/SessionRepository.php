<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Level;
use App\Entity\Session;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
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

    public function findOneByUserAndClusters(User $user, Collection $clusers): ?Session
    {
        try {
            return $this->createQueryBuilder('s')
            ->andWhere(
                (new Expr())->in('s.cluster', ':clusers'),
                (new Expr())->eq('s.user', ':user'),
            )
            ->setParameter('clusers', $clusers)
            ->setParameter('user', $user)
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
            )
            ->setParameter('bikeRideId', $bikeRideId)
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
            ->setParameter('levelType', Level::TYPE_FRAME)
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
            $parameters['startAt'] = $filters['startAt'];
            $parameters['endAt'] = $filters['endAt'];
        }

        if (isset($filters['bikeRideType'])) {
            $andX->add((new Expr())->eq('br.bikeRideType', ':bikeRideType'));
            $parameters['bikeRideType'] = $filters['bikeRideType'];
        }

        if (isset($filters['levels'])) {
            $this->addCriteriaByLevel($andX, $parameters, $filters['levels']);
        }

        return $this->createQueryBuilder('s')
            ->leftJoin('s.cluster', 'c')
            ->leftJoin('c.bikeRide', 'br')
            ->leftJoin('s.user', 'u')
            ->leftJoin('u.level', 'l')
            ->andWhere($andX)
            ->setParameters($parameters)
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
                Level::TYPE_ALL_MEMBER => $types[] = Level::TYPE_SCHOOL_MEMBER,
                Level::TYPE_ALL_FRAME => $types[] = Level::TYPE_FRAME,
                Level::TYPE_BOARD_MEMBER => $isBoardmember = true,
                default => $levels[] = $level,
            };
        }
 
        $orX = (new Expr())->orX();
        if (!empty($levels)) {
            $orX->add((new Expr())->in('u.level', ':levels'));
            $parameters['levels'] = $levels;
        }

        if (!empty($types)) {
            $orX->add((new Expr())->in('l.type', ':types'));
            $parameters['types'] = $types;
        }

        if ($isBoardmember) {
            $orX->add((new Expr())->isNotNull('u.boardRole'));
        }

        if (0 < $orX->count()) {
            $andX->add($orX);
        }
    }

    public function findOfTheDayByUser(User $user): ?Session
    {
        try {
            $today = new DateTimeImmutable();
            return $this->createQueryBuilder('s')
                ->join('s.cluster', 'c')
                ->join('c.bikeRide', 'br')
                ->andWhere(
                    (new Expr())->eq('s.availability', ':availability'),
                    (new Expr())->eq('s.user', ':user'),
                    (new Expr())->between('br.startAt', ':start', ':end'),
                )
                ->setParameters([
                    'availability' => Session::AVAILABILITY_REGISTERED,
                    'user' => $user,
                    'start' => $today->setTime(0, 0, 0),
                    'end' => $today->setTime(18, 0, 0),
                ])
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findMemberpresence(array $filters): array
    {
        $parameters = ['isPresent' => true];
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('s.isPresent', ':isPresent'));

        if (array_key_exists('isSchool', $filters)) {
            $andX->add((new Expr())->eq('brt.needFramers', ':needFramers'));
            $parameters['needFramers'] = $filters['isSchool'];
        }

        if (array_key_exists('period', $filters) && !empty($filters['period'])) {
            if (is_array($filters['period'])) {
                $parameters['startAt'] = $filters['period']['startAt'];
                $parameters['endAt'] = $filters['period']['endAt'];
            }
            if (is_string($filters['period'])) {
                list($startAt, $endAt) = explode('-', $filters['period']);
                $parameters['startAt'] = DateTimeImmutable::createFromFormat('d/m/Y', trim($startAt));
                $parameters['endAt'] = DateTimeImmutable::createFromFormat('d/m/Y', trim($endAt));
            }
            $andX->add((new Expr())->between('br.startAt', ':startAt', ':endAt'));
        }

        return $this->createQueryBuilder('s')
            ->select((new Expr())->count('s.isPresent'), 'br.startAt')
            ->join('s.cluster', 'c')
            ->join('c.bikeRide', 'br')
            ->join('br.bikeRideType', 'brt')
            ->andWhere($andX)
            ->setParameters($parameters)
            ->groupBy('c.bikeRide')
            ->orderBy('br.startAt')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAvailableByUser(User $user): array
    {
        $today = new DateTimeImmutable();
        return $this->createQueryBuilder('s')
            ->join('s.cluster', 'c')
            ->join('c.bikeRide', 'br')
            ->andWhere(
                (new Expr())->eq('s.user', ':user'),
                (new Expr())->gte('br.startAt', ':start'),
            )
            ->setParameters([
                'user' => $user,
                'start' => $today->setTime(0, 0, 0),
            ])
            ->getQuery()
            ->getResult();
    }
}
