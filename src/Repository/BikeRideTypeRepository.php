<?php

namespace App\Repository;

use App\Entity\BikeRideType;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
}
