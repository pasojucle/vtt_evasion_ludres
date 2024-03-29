<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Level;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
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

    public function findLevelQuery(int $type): QueryBuilder
    {
        return $this->createQueryBuilder('l')
            ->andWhere(
                (new Expr())->eq('l.type', ':type'),
                (new Expr())->eq('l.isDeleted', ':isDeleted'),
            )
            ->setParameter('type', $type)
            ->setParameter('isDeleted', false)
            ->orderBy('l.orderBy', 'ASC')
            ->addOrderBy('l.title', 'ASC')
        ;
    }

    /**
     * @return Level[] Returns an array of Level objects
     */
    public function findByType(int $type): array
    {
        $qb = $this->findLevelQuery($type);

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Level[] Returns an array of Level objects
     */
    public function findAllTypeMember(): array
    {
        $qb = $this->findLevelQuery(Level::TYPE_SCHOOL_MEMBER);

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Level[] Returns an array of Level objects
     */
    public function findAllTypeMemberNotProtected(): array
    {
        $qb = $this->findLevelQuery(Level::TYPE_SCHOOL_MEMBER);

        return $qb
            ->andWhere(
                $qb->expr()->eq('l.isProtected', 0)
            )
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Level[] Returns an array of Level objects
     */
    public function findAllTypeFramer(): array
    {
        $qb = $this->findLevelQuery(Level::TYPE_FRAME);

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Level[] Returns an array of Level objects
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere(
                (new Expr())->eq('l.isDeleted', ':isDeleted'),
            )
            ->setParameter('isDeleted', false)
            ->addOrderBy('l.type', 'ASC')
            ->addOrderBy('l.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNexOrderByType(int $type): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('l')
            ->select('MAX(l.orderBy)')
            ->andWhere(
                (new Expr())->eq('l.type', ':type')
            )
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (null !== $maxOrder) {
            $maxOrder = (int) $maxOrder;
            $nexOrder = $maxOrder + 1;
        }

        return $nexOrder;
    }

    private function findDefaultByType(int $type): ?Level
    {
        try {
            return $this->createQueryBuilder('l')
                ->andWhere(
                    (new Expr())->eq('l.type', ':type'),
                    (new Expr())->eq('l.isProtected', 1)
                )
                ->setParameter('type', $type)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findAwaitingEvaluation(): ?Level
    {
        return $this->findDefaultByType(Level::TYPE_SCHOOL_MEMBER);
    }

    public function findUnframedAdult(): ?Level
    {
        return $this->findDefaultByType(Level::TYPE_ADULT_MEMBER);
    }
}
