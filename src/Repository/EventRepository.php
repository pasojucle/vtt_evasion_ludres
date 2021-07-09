<?php

namespace App\Repository;

use App\Entity\Event;
use DateTime;
use Doctrine\ORM\Query\Expr;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\DBAL\Query\QueryBuilder as QueryQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
            ->orderBy('e.startAt', 'ASC')
        ;
    }


    /**
     * @return User[] Returns an array of enent objects
     */

    public function findEnableView(): array
    {
        $today = new DateTime();
        $today =  DateTime::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d').' 00:00:00');

        return $this->createQueryBuilder('e')
            ->andWhere(
                (new Expr)->gte('e.startAt', ':today'),

            )
            ->setParameter('today', $today)
            ->orderBy('e.startAt', 'ASC')
            ->andHaving("DATE_SUB(e.startAt, e.displayDuration, 'DAY') <= :today")
            ->getQuery()
            ->getResult();
        ;
    }
}
