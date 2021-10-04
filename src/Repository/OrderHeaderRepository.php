<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\OrderHeader;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method OrderHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderHeader[]    findAll()
 * @method OrderHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderHeaderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderHeader::class);
    }

    public function findOneOrderByUserAndStatus(User $user, int $status): ?OrderHeader
    {
        try {
            return $this->createQueryBuilder('oh')
            ->andWhere(
                (new Expr)->lt('oh.status', ':status'),
                (new Expr)->eq('oh.user', ':user'),
            )
            ->setParameter('status', $status)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
            ;
        } catch(NonUniqueResultException $e) {
            return null;
        }
    }
}
