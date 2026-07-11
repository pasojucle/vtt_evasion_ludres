<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findProductQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p');
    }

    public function filterDisabled(QueryBuilder $qb): void
    {
        $qb
        ->andWhere(
            $qb->expr()->isNotNull('p.disabledAt')
        );
    }

    public function filterEnabled(QueryBuilder $qb): void
    {
        $qb
        ->andWhere(
            $qb->expr()->isNull('p.disabledAt')
        );
    }

    public function filterPartNumber(QueryBuilder $qb, string $partNumber): void
    {
        $qb
            ->andWhere(
                $qb->expr()->like('p.ref', ':partNumber')
            )
            ->setParameter('partNumber', '%' . $partNumber . '%');
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('p.name', $direction);
    }

    public function filterActive(QueryBuilder $qb): void
    {
        $qb->andWhere(
            $qb->expr()->isNull('p.deletedAt')
        );
    }

    public function findAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where(
                (new Expr())->isNull('p.deletedAt')
            )
            ->orderBy('p.name', 'ASC')
        ;
    }
}
