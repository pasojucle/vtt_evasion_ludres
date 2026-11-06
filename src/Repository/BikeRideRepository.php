<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Member;
use App\Entity\Session;
use App\Entity\Survey;
use App\Repository\Interface\BikeRideRepositoryInterface;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BikeRide|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeRide|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeRide[]    findAll()
 * @method BikeRide[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeRideRepository extends ServiceEntityRepository implements BikeRideRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeRide::class);
    }

    public function save(BikeRide $bikeRide, bool $flush = true): void
    {
        $this->getEntityManager()->persist($bikeRide);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BikeRide $bikeRide, bool $flush = true): void
    {
        $this->getEntityManager()->remove($bikeRide);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    private function hasJoin(QueryBuilder $qb, string $alias): bool
    {
        $joins = $qb->getDQLPart('join');
        
        foreach ($joins as $joinList) {
            /** @var Join $join */
            foreach ($joinList as $join) {
                if ($join->getAlias() === $alias) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return QueryBuilder
     */
    public function findAllQuery(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('br');
        $andX = $qb->expr()->andX();
        if (null !== $filters['startAt']) {
            $andX->add($qb->expr()->gte('br.startAt', ':startAt'));
            $qb->setParameter('startAt', $filters['startAt']);
            if (null === $filters['endAt']) {
                $qb->setMaxResults($filters['limit']);
            }
        }
        if (null !== $filters['endAt']) {
            $andX->add($qb->expr()->lte('br.startAt', ':endAt'));
            $qb->setParameter('endAt', $filters['endAt']);
        }

        $andX->add((new Expr())->isNull('br.deletedAt'), );
        $qb->andWhere($andX);

        return $qb
            ->orderBy('br.startAt', 'ASC')
        ;
    }

    public function getActivityQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('a');
    }

    public function getTotalSessionsByActivityQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->select(
                'a as activity',
                'COUNT(se.isPresent) as total',
            )
            ->groupBy('a.id');
    }

    public function filterParticipated(QueryBuilder $qb): void
    {
        if (!$this->hasJoin($qb, 'cl')) {
            $qb->join('a.clusters', 'cl')->addSelect('cl');
        }

        if (!$this->hasJoin($qb, 'se')) {
            $qb->join('cl.sessions', 'se')->addSelect('se');
        }

        $qb->andWhere(
            $qb->expr()->eq('se.isPresent', ':isPresent')
        )
        ->setParameter('isPresent', true);
    }

    public function filterPeriod(QueryBuilder $qb, DateTimeImmutable $startAt, DateTimeImmutable $endAt): void
    {
        $qb
            ->andWhere(
                $qb->expr()->between('a.startAt', ':startAt', ':endAt')
            )
            ->setParameter('startAt', $startAt)
            ->setParameter('endAt', $endAt);
    }

    public function filterQuery(QueryBuilder $qb, string $term): void
    {
        $orX = $qb->expr()->orX();
        foreach (explode(' ', $term) as $key => $word) {
            $parameterName = 'word_' . $key;
            $orX->add(
                $qb->expr()->like('a.title', ':word_' . $key)
            );
            $qb->setParameter($parameterName, '%' . $word . '%');

            $date = DateTimeImmutable::createFromFormat('d/m/y', $word)
                ?: DateTimeImmutable::createFromFormat('d/m/Y', $word)
                ?: DateTimeImmutable::createFromFormat('Y-m-d', $word);

            if ($date !== false) {
                $dateParamName = 'date_' . $key;
                $startOfDay = $date->setTime(0, 0, 0);
                $endOfDay = $date->setTime(23, 59, 59);

                $orX->add($qb->expr()->between('a.startAt', ':' . $dateParamName . '_start', ':' . $dateParamName . '_end'));
                $qb->setParameter($dateParamName . '_start', $startOfDay);
                $qb->setParameter($dateParamName . '_end', $endOfDay);
            }
        }

        $qb->andWhere($orX);
    }

    public function filterLevels(QueryBuilder $qb, array $types, array $ids): void
    {
        if (!$this->hasJoin($qb, 'usr')) {
            $qb->join('se.user', 'usr')->addSelect('usr');
        }

        if (!$this->hasJoin($qb, 'le')) {
            $qb->leftJoin('usr.level', 'le')->addSelect('le');
        }
        $orX = $qb->expr()->orX();

        if (!empty($types)) {
            $orX->add($qb->expr()->in('le.type', ':types'));
            $qb->setParameter('types', $types);
        }

        if (!empty($ids)) {
            $orX->add($qb->expr()->in('le.id', ':ids'));
            $qb->setParameter('ids', $ids);
        }

        if ($orX->count() > 0) {
            $qb->andWhere($orX);
        }
    }

    public function filterPractice(QueryBuilder $qb, PracticeEnum $practice): void
    {
        $qb->andWhere(
            $qb->expr()->eq('se.practice', ':practice')
        )
        ->setParameter('practice', $practice);
    }

    public function filterUpcoming(QueryBuilder $qb, DateTimeInterface $today): void
    {
        $qb->andWhere(
            $qb->expr()->gte('a.startAt', ':today'),
            $qb->expr()->isNull('a.deletedAt'),
        )
        ->setParameter('today', $today);
    }

    public function filterByMonth(QueryBuilder $qb, DateTimeInterface $startAt, DateTimeInterface $endAt): void
    {
        $qb->andWhere(
            $qb->expr()->gte('a.startAt', ':startAt'),
            $qb->expr()->lte('a.startAt', ':endAt'),
            $qb->expr()->isNull('a.deletedAt'),
        )
        ->setParameter('startAt', $startAt)
        ->setParameter('endAt', $endAt);
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('a.startAt', $direction);
    }

    public function filterType(QueryBuilder $qb, BikeRideType $type): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('a.bikeRideType', ':type')
            )
            ->setParameter('type', $type);
    }

    public function filterIsPrivate(QueryBuilder $qb, bool $isPrivate): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('a.private', ':isPrivate')
            )
            ->setParameter('isPrivate', $isPrivate);
    }

    public function filterHasMembers(QueryBuilder $qb): void
    {
        $qb
            ->innerJoin('a.members', 'm')
            ->distinct();
    }

    public function filterHasAge(QueryBuilder $qb): void
    {
        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNotNull('a.minAge'),
                    $qb->expr()->isNotNull('a.maxAge'),
                )
            );
    }

    public function filterActive(QueryBuilder $qb): void
    {
        $qb->andWhere(
            $qb->expr()->isNull('a.deletedAt')
        );
    }

    /**
     * @return QueryBuilder
     */
    public function findByPeriodQuery(?DateTimeInterface $startAt, ?DateTimeInterface $endAt): QueryBuilder
    {
        $qb = $this->createQueryBuilder('br');
        $andX = $qb->expr()->andX();
        if ($startAt) {
            $andX->add($qb->expr()->gte('br.startAt', ':startAt'));
            $qb->setParameter('startAt', $startAt);
        }
        if ($endAt) {
            $andX->add($qb->expr()->lte('br.startAt', ':endAt'));
            $qb->setParameter('endAt', $endAt);
        }

        $andX->add((new Expr())->isNull('br.deletedAt'), );

        return $qb
            ->andWhere($andX)
            ->orderBy('br.startAt', 'ASC')
        ;
    }

    /**
     * @return BikeRide[] Returns an array of enent objects
     */
    public function findAllFiltered(array $filters): array
    {
        /** @var QueryBuilder $qb */
        $qb = $this->findAllQuery($filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return BikeRide[] Returns an array of enent objects
     */
    public function findEnableView(): array
    {
        $today = new DateTime();
        $today = DateTime::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' 23:59:00');

        return $this->createQueryBuilder('br')
            ->andWhere(
                (new Expr())->gte('br.startAt', ':today'),
                (new Expr())->isNull('br.deletedAt', ),
            )
            ->setParameter('today', $today)
            ->orderBy('br.startAt', 'ASC')
            ->andHaving("DATE_SUB(br.startAt, br.displayDuration, 'DAY') <= :today")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return BikeRide[] Returns an array of enent objects
     */
    public function findLike(string $query): array
    {
        $params = [];
        $orX = (new Expr())->orX();
        $orX->add((new Expr())->like('br.title', ':title'));
        $params[] = new Parameter('title', '%' . $query . '%');
        if (1 === preg_match('#^(\d{1,2})\/(\d{1,2})(?:[\/]{0,1})(\d{0,2})#', $query, $matches)) {
            list($all, $day, $month, $year) = $matches;
            $startAt = (empty($year))
                ? DateTimeImmutable::createFromFormat('m-d', sprintf('%s-%s', $month, $day))
                : DateTimeImmutable::createFromFormat('y-m-d', sprintf('%s-%s-%s', $year, $month, $day));
            $orX->add((new Expr())->eq('br.startAt', ':query'));
            $params[] = new Parameter('query', $startAt->setTime(0, 0, 0));
        }

        return $this->createQueryBuilder('br')
            ->andWhere(
                $orX,
                (new Expr())->isNull('br.deletedAt'),
                (new Expr())->notIn('br', $this->getBikeRideWithSurvey()),
            )
            ->setParameters(new ArrayCollection($params))
            ->orderBy('br.startAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return BikeRide[] Returns an array of enent objects
     */
    public function findAllDESC(): array
    {
        return $this->createQueryBuilder('br')
            ->andWhere(
                (new Expr())->isNull('br.deletedAt'),
                (new Expr())->notIn('br', $this->getBikeRideWithSurvey()),
            )
            ->orderBy('br.startAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    private function getBikeRideWithSurvey(): string
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('(s.bikeRide)')
            ->from(Survey::class, 's')
            ->where(
                (new Expr())->isNotNull('s.bikeRide')
            )
            ->getDQL();
    }

    public function findNextBikeRides(): array
    {
        return $this->createQueryBuilder('br')
            ->join('br.bikeRideType', 'brt')
            ->andWhere(
                (new Expr())->gte('br.startAt', ':start'),
                (new Expr())->lte('br.startAt', ':end'),
                (new Expr())->neq('brt.registration', ':registration'),
                (new Expr())->isNull('br.deletedAt'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('start', (new DateTimeImmutable())->setTime(0, 0, 0)),
                new Parameter('end', (new DateTimeImmutable())->add((new DateInterval('P7D')))->setTime(23, 59, 59)),
                new Parameter('registration', RegistrationEnum::NONE),
            ]))
            ->orderBy('br.startAt')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNotifiable(Member $member): array
    {
        $today = (new DateTimeImmutable())->setTime(23, 59, 00);
        $memberAge = (int) $today->diff($member->getIdentity()->getBirthDate())->format('%Y');

        $bikeRides = $this->getEntityManager()->createQueryBuilder()
            ->select('bikeRide.id')
            ->from(Session::class, 's')
            ->join('s.cluster', 'c')
            ->join('c.bikeRide', 'bikeRide')
            ->andWhere(
                (new Expr())->eq('s.user', ':member'),
            );

        return $this->createQueryBuilder('br')
            ->leftJoin('br.members', 'm')
            ->andWhere(
                (new Expr())->eq('br.notify', ':notify'),
                (new Expr())->orX(
                    (new Expr())->eq('m', ':member'),
                    (new Expr())->isNull('m'),
                ),
                (new Expr())->orX(
                    (new Expr())->lte('br.minAge', ':age'),
                    (new Expr())->isNull('br.minAge'),
                ),
                (new Expr())->orX(
                    (new Expr())->gte('br.maxAge', ':age'),
                    (new Expr())->isNull('br.maxAge'),
                ),
                (new Expr())->notIn('br.id', $bikeRides->getDql()),
                (new Expr())->gte('br.startAt', ':today'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('today', $today),
                new Parameter('notify', true),
                new Parameter('member', $member),
                new Parameter('age', $memberAge),

            ]))
            ->andHaving("DATE_SUB(br.startAt, br.displayDuration, 'DAY') <= :today")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findYearlyPublics(): array
    {
        return $this->createQueryBuilder('br')
            ->join('br.bikeRideType', 'brt')
            ->andWhere(
                (new Expr())->eq('brt.public', ':isPublic'),
                (new Expr())->gte('br.startAt', ':firstJan')
            )
            ->setParameter('isPublic', true)
            ->setParameter('firstJan', (new DateTime('January 1st'))->format(DateTime::ATOM))
            ->getQuery()
            ->getResult();
    }
}
