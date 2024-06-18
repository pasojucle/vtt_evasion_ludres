<?php

namespace App\Repository;

use App\Entity\BikeRide;
use App\Entity\Log;
use App\Entity\Summary;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
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
    public function findLatestDesc(?DateTimeImmutable $viewAt = null): array
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

        $andX = (new Expr())->andX();
        $andX->add((new Expr())->in('br.id', ':bikeRideLatest'));
        $parameters = [new Parameter('bikeRideLatest', $bikeRideLatest->getQuery()->getScalarResult())];

        if ($viewAt) {
            $andX->add((new Expr())->gt('s.createdAt', ':viewAt'));
            $parameters[] = new Parameter('viewAt', $viewAt);
        }

        return $this->createQueryBuilder('s')
            ->join('s.bikeRide', 'br')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
       ;
    }

    /**
     * @return Summary[] Returns an array of Summary objects
     */
    public function findNotViewedByUser(User $user): array
    {
        $viewed = $this->getEntityManager()->createQueryBuilder()
            ->select('log.entityId')
            ->from(Log::class, 'log')
            ->andWhere(
                (new Expr())->eq('log.user', ':user'),
                (new Expr())->eq('log.entity', ':entityName')
            );

        return $this->createQueryBuilder('s')
            ->andWhere(
                (new Expr())->notIn('s.id', $viewed->getDQL())
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('entityName', 'Summary')
            ]))
            ->getQuery()
            ->getResult()
       ;
    }
}
