<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\Cluster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cluster|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cluster|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cluster[]    findAll()
 * @method Cluster[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClusterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cluster::class);
    }

    /**
     * @return Cluster[] Returns an array of Cluster objects
     */
    public function findByBikeRide(BikeRide $bikeRide): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere(
                (new Expr())->eq('c.bikeRide', ':bikeRide')
            )
            ->setParameter('bikeRide', $bikeRide)
            ->getQuery()
            ->getResult()
        ;
    }
}
