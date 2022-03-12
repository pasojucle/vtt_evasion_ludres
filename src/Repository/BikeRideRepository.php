<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use DateTime;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeRide::class);
    }

    /**
     * @return BikeRide[] Returns an array of BikeRide objects
     */
    public function findAllQuery(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e');
        $andX = $qb->expr()->andX();
        if (null !== $filters['startAt']) {
            $andX->add($qb->expr()->gte('e.startAt', ':startAt'));
            $qb->setParameter('startAt', $filters['startAt']);
            if (null === $filters['endAt']) {
                $qb->setMaxResults(6);
            }
        }
        if (null !== $filters['endAt']) {
            $andX->add($qb->expr()->lte('e.startAt', ':endAt'));
            $qb->setParameter('endAt', $filters['endAt']);
        }

        if (!empty($andX->getParts())) {
            $qb->andWhere($andX);
        }

        return $qb
            ->orderBy('e.startAt', 'ASC')
        ;
    }

    /**
     * @return BikeRide[] Returns an array of enent objects
     */
    public function findAllFiltered(array $filters): array
    {
        $qb = $this->findAllQuery($filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return BikeRide[] Returns an array of enent objects
     */
    public function findEnableView(): array
    {
        $today = new DateTime();
        $today = DateTime::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d').' 23:59:00');

        return $this->createQueryBuilder('e')
            ->andWhere(
                (new Expr())->gte('e.startAt', ':today'),
            )
            ->setParameter('today', $today)
            ->orderBy('e.startAt', 'ASC')
            ->andHaving("DATE_SUB(e.startAt, e.displayDuration, 'DAY') <= :today")
            ->getQuery()
            ->getResult()
        ;
    }
}
