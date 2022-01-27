<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vote;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Vote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vote[]    findAll()
 * @method Vote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    /**
     * @return Vote[] Returns an array of Vote objects
     */

    public function findActiveQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('v')
            ->andWhere(
                (new Expr())->eq('v.disabled', 0),
                (new Expr())->lte('v.startAt', 'CURRENT_DATE()'),
                (new Expr())->gte('v.endAt', 'CURRENT_DATE()'),
            )
            ->orderBy('v.id', 'ASC')
        ;
    }

    public function findActive()
    {
        $qb = $this->findActiveQuery();
        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}
