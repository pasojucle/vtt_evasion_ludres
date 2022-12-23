<?php

namespace App\Repository;

use App\Entity\BoardRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BoardRole>
 *
 * @method BoardRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoardRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoardRole[]    findAll()
 * @method BoardRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoardRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoardRole::class);
    }

    public function save(BoardRole $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BoardRole $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBoardRoleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('br')
            ->orderBy('br.orderBy', 'ASC')
            ->addOrderBy('br.name', 'ASC')
        ;
    }

    public function findAllOrdered(): array
    {
        return $this->findBoardRoleQuery()
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNexOrder(): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('br')
            ->select('MAX(br.orderBy)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (null !== $maxOrder) {
            $maxOrder = (int) $maxOrder;
            $nexOrder = $maxOrder + 1;
        }

        return $nexOrder;
    }
}
