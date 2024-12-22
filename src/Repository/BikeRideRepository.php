<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Survey;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BikeRide|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeRide|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeRide[]    findAll()
 * @method BikeRide[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeRideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeRide::class);
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

        $andX->add((new Expr())->eq('br.deleted', ':deleted'), );
        $qb->setParameter('deleted', 0);
        $qb->andWhere($andX);

        return $qb
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
                (new Expr())->eq('br.deleted', ':deleted'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('today', $today),
                new Parameter('deleted', 0)
            ]))
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
        $params['deleted'] = new Parameter('deleted', false);
        return $this->createQueryBuilder('br')
            ->andWhere(
                $orX,
                (new Expr())->eq('br.deleted', ':deleted'),
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
                (new Expr())->eq('br.deleted', ':deleted'),
                (new Expr())->notIn('br', $this->getBikeRideWithSurvey()),
            )
            ->setParameter('deleted', 0)
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
                (new Expr())->eq('br.deleted', ':deleted'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('start', (new DateTimeImmutable())->setTime(0, 0, 0)),
                new Parameter('end', (new DateTimeImmutable())->add((new DateInterval('P7D')))->setTime(23, 59, 59)),
                new Parameter('registration', RegistrationEnum::NONE),
                new Parameter('deleted', false),
            ]))
            ->orderBy('br.startAt')
            ->getQuery()
            ->getResult()
        ;
    }
}
