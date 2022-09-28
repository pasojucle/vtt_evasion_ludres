<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\Session;
use App\Entity\User;
use App\Service\SeasonService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private SeasonService $seasonService)
    {
        parent::__construct($registry, Session::class);
    }

    public function findByUserAndClusters(User $user, Collection $clusers): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere(
                (new Expr())->in('s.cluster', ':clusers'),
                (new Expr())->eq('s.user', ':user'),
            )
            ->setParameter('clusers', $clusers)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByBikeRide(BikeRide $bikeRide): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.cluster', 'c')
            ->andWhere(
                (new Expr())->eq('c.bikeRide', ':bikeRide'),
            )
            ->setParameter('bikeRide', $bikeRide)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByUserAndFilters(User $user, array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->leftJoin('s.cluster', 'c')
            ->leftJoin('c.bikeRide', 'br')
            ->andWhere(
                $qb->expr()->eq('s.user', ':user')
            )
            ->setParameter('user', $user)
            ;
        if (isset($filters['season'])) {
            $season = (int) str_replace('SEASON_', '', $filters['season']);
            $interval = $this->seasonService->getSeasonInterval($season);
            $qb
                ->andWhere(
                    $qb->expr()->between('br.startAt', ':startAt', ':endAt')
                )
                ->setParameter('startAt', $interval['startAt'])
                ->setParameter('endAt', $interval['endAt']);
        }
        if (isset($filters['bikeRideType'])) {
            $qb
                ->andWhere(
                    $qb->expr()->eq('br.bikeRideType', ':bikeRideType')
                )
                ->setParameter('bikeRideType', $filters['bikeRideType']);
        }

        return $qb;
    }
}
