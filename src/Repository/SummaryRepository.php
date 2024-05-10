<?php

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\Summary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Summary>
 *
 * @method Summary|null find($id, $lockMode = null, $lockVersion = null)
 * @method Summary|null findOneBy(array $criteria, array $orderBy = null)
 * @method Summary[]    findAll()
 * @method Summary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SummaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Summary::class);
    }

    /**
     * @return Summary[] Returns an array of Summary objects
     */
    public function findLatestDesc(): array
    {
        $bikeRideLatest = $this->getEntityManager()->createQueryBuilder()
            ->select('bikeRide.id')
            ->from(BikeRide::class, 'bikeRide')
            ->join('bikeRide.summaries', 'summary')
            ->andWhere(
                (new Expr())->isNotNull('summary'),
            )
            ->setMaxResults(2)
            ->groupBy('bikeRide.id')
            ->orderBy('bikeRide.startAt', 'DESC');

        return $this->createQueryBuilder('s')
            ->join('s.bikeRide', 'br')
            ->andWhere(
                (new Expr())->in('br.id', ':bikeRideLatest')
            )
            ->setParameter('bikeRideLatest', $bikeRideLatest->getQuery()->getScalarResult())
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
       ;
    }
}
