<?php

namespace App\Repository;

use App\Entity\Level;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Level|null find($id, $lockMode = null, $lockVersion = null)
 * @method Level|null findOneBy(array $criteria, array $orderBy = null)
 * @method Level[]    findAll()
 * @method Level[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Level::class);
    }

    /**
     * @return Level[] Returns an array of Level objects
     */

    public function findAllTypeMember():array
    {
        return $this->createQueryBuilder('l')
            ->andWhere(
                (new Expr)->eq('l.type', Level::TYPE_MEMBER)
            )
            ->orderBy('l.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Level[] Returns an array of Level objects
     */

    public function findAllTypeFramer():array
    {
        return $this->createQueryBuilder('l')
            ->andWhere(
                (new Expr)->eq('l.type', Level::TYPE_FRAME)
            )
            ->orderBy('l.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return Level[] Returns an array of Level objects
     */

    public function findAll():array
    {
        return $this->createQueryBuilder('l')
            ->addOrderBy('l.type', 'ASC')
            ->addOrderBy('l.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
