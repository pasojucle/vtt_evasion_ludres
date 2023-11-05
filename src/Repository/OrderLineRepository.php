<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderLine[]    findAll()
 * @method OrderLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderLine::class);
    }

    public function deleteByOrderHeader(OrderHeader $orderHeader): void
    {
        $this->createQueryBuilder('ol')
        ->delete()
        ->andWhere(
            (new Expr())->eq('ol.orderHeader', ':orderHeader')
        )
        ->setParameter('orderHeader', $orderHeader)
        ->getQuery()
        ->getResult()
    ;
    }
}
