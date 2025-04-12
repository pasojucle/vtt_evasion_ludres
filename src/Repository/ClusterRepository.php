<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Log;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
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

    public function findAvailableByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->leftjoin(Log::class, 'log', 'WITH', (new Expr())->andX((new Expr())->eq('c.id', 'log.entityId'), (new Expr())->eq('log.entity', ':entityName'), (new Expr())->eq('log.user', ':user')))
            ->join('c.bikeRide', 'br')
            ->join('c.sessions', 's')
            ->andWhere(
                (new Expr())->isNull('log'),
                (new Expr())->eq('s.user', ':user'),
                (new Expr())->gte('br.startAt', ':start'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('entityName', 'Cluster'),
                new Parameter('start', (new DateTimeImmutable())->setTime(0, 0, 0)),
            ]))
            ->getQuery()
            ->getResult()
            ;
    }
}
