<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Enum\ProductStateEnum;
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

    public function findProductQuery(?ProductStateEnum $state): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->where(
                (new Expr())->eq('p.deleted', ':isDeleted'),
            )
            ->setParameter('isDeleted', false)
            ->orderBy('p.name', 'ASC')
        ;
        if ($state) {
            $qb
                ->andWhere(
                    $qb->expr()->eq('p.isDisabled',':state')
                )
                ->setParameter('state', ProductStateEnum::DISABLED === $state);
        }

        return $qb;
    }
}
