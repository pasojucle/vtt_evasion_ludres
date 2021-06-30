<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\QueryBuilder;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query\QueryBuilder as QueryQueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[] Returns an array of Event objects
     */

    public function findAllQuery(array $filters): QueryBuilder
    {

        $qb = $this->createQueryBuilder('e');
        if (null !== $filters['startAt'] && null !== $filters['endAt']) {
            $qb->andWhere(
                $qb->expr()->gte('e.startAt', ':startAt'),
                $qb->expr()->lte('e.startAt', ':endAt')
            )
            ->setParameter('startAt', $filters['startAt'])
            ->setParameter('endAt', $filters['endAt'])
            ;
        }
        return $qb
            ->orderBy('e.startAt', 'DESC')
        ;
    }

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
