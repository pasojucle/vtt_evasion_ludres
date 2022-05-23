<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Survey|null find($id, $lockMode = null, $lockVersion = null)
 * @method Survey|null findOneBy(array $criteria, array $orderBy = null)
 * @method Survey[]    findAll()
 * @method Survey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Survey::class);
    }

    /**
     * @return Survey[] Returns an array of Survey objects
     */
    public function findAllDESCQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.id', 'DESC')
        ;
    }


    /**
     * @return Survey[] Returns an array of Survey objects
     */
    public function findActiveQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('v')
            ->andWhere(
                (new Expr())->eq('v.disabled', 0),
                (new Expr())->lte('v.startAt', 'CURRENT_DATE()'),
                (new Expr())->gte('v.endAt', 'CURRENT_DATE()'),
                (new Expr())->isNull('v.bikeRide'),
            )
            ->orderBy('v.id', 'ASC')
        ;
    }

    public function findActive(): array
    {
        /**@var QueryBuilder $qb */
        $qb = $this->findActiveQuery();

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}
