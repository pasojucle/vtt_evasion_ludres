<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Service\SeasonService;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
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
    public function __construct(ManagerRegistry $registry, private SeasonService $seasonService)
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

        $andX->add((new Expr())->eq('br.deleted', ':deleted'),);
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
            ->setParameter('today', $today)
            ->setParameter('deleted', 0)
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
        if (1 === preg_match('#^(\d{2})\/(\d{1,2})#', $query)) {
            $today = new DateTime();
            $query = $query . '/' . $today->format('y');
        }
        $startAt = DateTimeImmutable::createFromFormat('d/m/y', $query);
        $orX = (new Expr())->orX();
        $orX->add((new Expr())->like('br.title', ':title'));
        $params['title'] = '%' . $query . '%';
        if ($startAt) {
            $orX->add((new Expr())->eq('br.startAt', ':query'));
            $params['query'] = $startAt->setTime(0, 0, 0);
        }
        $params['deleted'] = 0;

        return $this->createQueryBuilder('br')
            ->andWhere($orX)
            ->andWhere((new Expr())->eq('br.deleted', ':deleted'),)
            ->setParameters($params)
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
            ->andWhere((new Expr())->eq('br.deleted', ':deleted'),)
            ->setParameter('deleted', 0)
            ->orderBy('br.startAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
