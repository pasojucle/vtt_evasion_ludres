<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function findLinkQuery(int $position): QueryBuilder
    {
        return $this->createQueryBuilder('l')
        ->andWhere(
            (new Expr)->eq('l.position', ':position')
        )
        ->setParameter('position', $position)
        ->orderBy('l.orderBy', 'ASC')
        ->addOrderBy('l.title', 'ASC')
        ;
    }

    /**
     * @return User[] Returns an array of link objects
     */

    public function findByPosition(int $position): array
    {
        $qb = $this->findLinkQuery($position);
        return $qb
            ->getQuery()
            ->getResult();
        ;
    }

    public function findNexOrderByPosition(int $position): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('l')
            ->select('MAX(l.orderBy)')
            ->andWhere(
                (new Expr)->eq('l.position', ':position')
            )
            ->setParameter('position', $position)
            ->getQuery()
            ->getSingleScalarResult();
        ;

        if (null !== $maxOrder) {
            $maxOrder = (int) $maxOrder;
            $nexOrder = $maxOrder + 1;
        }

        return $nexOrder;
    }
}
