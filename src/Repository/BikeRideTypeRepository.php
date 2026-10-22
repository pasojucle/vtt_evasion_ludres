<?php

namespace App\Repository;

use App\Entity\BikeRideType;
use App\Repository\Interface\ActivityTypeRepositoryInterface;
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
class BikeRideTypeRepository extends ServiceEntityRepository implements ActivityTypeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeRideType::class);
    }

    public function save(BikeRideType $bikeRideType, bool $flush = true): void
    {
        $this->getEntityManager()->persist($bikeRideType);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BikeRideType $bikeRideType, bool $flush = true): void
    {
        $this->getEntityManager()->remove($bikeRideType);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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
            ->andWhere(
                (new Expr())->eq('brt.needFramers', ':needFramers')
            )
            ->setParameter('needFramers', true)
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

    public function filterActive(QueryBuilder $qb): void
    {
        $qb->andWhere(
            $qb->expr()->isNull('brt.deletedAt')
        );
    }
}
