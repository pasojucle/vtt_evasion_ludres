<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Level;
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
                Level::TYPE_ALL_MEMBER => $types[] = Level::TYPE_SCHOOL_MEMBER,
                Level::TYPE_ALL_FRAME => $types[] = Level::TYPE_FRAME,
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
                ->setParameters(new ArrayCollection([
                    new Parameter('availability', Session::AVAILABILITY_REGISTERED),
                    new Parameter('user', $user),
                    new Parameter('start', $today->setTime(0, 0, 0)),
                    new Parameter('end', $today->setTime(18, 0, 0)),
                ]))
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findMemberpresence(array $filters): array
    {
        $parameters = [new Parameter('isPresent', true)];
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('s.isPresent', ':isPresent'));

        if (array_key_exists('isSchool', $filters)) {
            $andX->add((new Expr())->eq('brt.needFramers', ':needFramers'));
            $parameters[] = new Parameter('needFramers', $filters['isSchool']);
        }

        if (array_key_exists('period', $filters) && !empty($filters['period'])) {
            if (is_array($filters['period'])) {
                $parameters[] = new Parameter('startAt', $filters['period']['startAt']);
                $parameters[] = new Parameter('endAt', $filters['period']['endAt']);
            }
            if (is_string($filters['period'])) {
                list($startAt, $endAt) = explode('-', $filters['period']);
                $parameters[] = new Parameter('startAt', DateTimeImmutable::createFromFormat('d/m/Y', trim($startAt)));
                $parameters[] = new Parameter('endAt', DateTimeImmutable::createFromFormat('d/m/Y', trim($endAt)));
            }
            $andX->add((new Expr())->between('br.startAt', ':startAt', ':endAt'));
        }

        return $this->createQueryBuilder('s')
            ->select((new Expr())->count('s.isPresent'), 'br.startAt')
            ->join('s.cluster', 'c')
            ->join('c.bikeRide', 'br')
            ->join('br.bikeRideType', 'brt')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
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
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
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
                new Parameter('start', (new DateTimeImmutable())->setTime(0, 0, 0)),
                new Parameter('end', (new DateTimeImmutable())->add((new DateInterval('P7D')))->setTime(23, 59, 59)),
                new Parameter('registration', 0),
                new Parameter('deleted', false),
                new Parameter('needFramers', true),
                new Parameter('avaylability', AvailabilityEnum::AVAILABLE),
            ]))
            ->getQuery()
            ->getResult();
    }
}
