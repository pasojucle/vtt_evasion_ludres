<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LogError;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogError|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogError|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogError[]    findAll()
 * @method LogError[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogErrorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogError::class);
    }

    /**
     * @return QueryBuilder
     */
    public function findLogErrorQuery(int $statusCode): QueryBuilder
    {
        return $this->createQueryBuilder('l')
            ->andWhere(
                (new Expr())->eq('l.statusCode', ':statusCode')
            )
            ->setParameter('statusCode', $statusCode)
            ->orderBy('l.createdAt', 'DESC')
        ;
    }

    public function deletAllBySatusCode(int $statusCode): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->where('l.statusCode = :statusCode')
            ->setParameter('statusCode', $statusCode)
            ->getQuery()
            ->getResult()
            ;
    }
}
