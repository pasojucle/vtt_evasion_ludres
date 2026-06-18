<?php

namespace App\Repository;

use App\Entity\BikeRideType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BikeRideType|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeRideType|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeRideType[]    findAll()
 * @method BikeRideType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeRideTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeRideType::class);
    }

    /**
     * @return BikeRideType[] Returns an array of BikeRideType objects
     */
    public function findCompensables(): array
    {
        return $this->createQueryBuilder('brt')
            ->andWhere(
                (new Expr())->eq('brt.isCompensable', ':value')
            )
            ->setParameter('value', true)
            ->orderBy('brt.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findBikeRideTypeQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('brt')
            ->orderBy('brt.name', 'ASC')
        ;
    }

    public function findDefault(): ?BikeRideType
    {
        try {
            return $this->createQueryBuilder('brt')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function getBikeRideTypeQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('brt');
    }

    public function filterName(QueryBuilder $qb, string $term): void
    {
        $qb
            ->andWhere(
                $qb->expr()->like('LOWER(brt.name)', ':term')
            )
            ->setParameter('term', '%' . strtolower($term) . '%');
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('brt.name', $direction);
    }
}
