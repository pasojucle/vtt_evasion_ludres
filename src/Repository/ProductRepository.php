<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use JetBrains\PhpStorm\Internal\ReturnTypeContract;

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

    public function findAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where(
                (new Expr)->eq('p.isDisabled', ':isDisabled')
            )
            ->setParameter('isDisabled', false)
            ->orderBy('p.name', 'ASC')
        ;
    }
}
