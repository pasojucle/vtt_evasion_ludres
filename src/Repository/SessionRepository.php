<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Session;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function findByUserAndClusters(User $user, Collection $clusers): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere(
                (new Expr)->in('s.cluster', ':clusers'),
                (new Expr)->eq('s.user', ':user'),
            )
            ->setParameter('clusers', $clusers)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByEvent(Event $event): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.cluster', 'c')
            ->andWhere(
                (new Expr)->eq('c.event', ':event'),
            )
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }
}
