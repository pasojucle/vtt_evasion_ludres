<?php

namespace App\Repository;

use App\Entity\SecondHandCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecondHandCategory>
 *
 * @method SecondHandCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecondHandCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecondHandCategory[]    findAll()
 * @method SecondHandCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecondHandCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecondHandCategory::class);
    }

    public function save(SecondHandCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SecondHandCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return SecondHandCategory[] Returns an array of SecondHandCategory objects
     */
    public function findAllAsc(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere(
                (new Expr())->eq('c.deleted', ':deleted')
            )
            ->setParameter('deleted', false)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getSecondHandCategoryQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('shc');
    }

    public function filterName(QueryBuilder $qb, string $term): void
    {
        $qb
            ->andWhere(
                $qb->expr()->like('LOWER(shc.name)', ':term')
            )
            ->setParameter('term', '%' . strtolower($term) . '%');
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('shc.name', $direction);
    }

    public function filterActive(QueryBuilder $qb): void
    {
        $qb->andWhere(
            $qb->expr()->isNull('shc.deletedAt')
        );
    }
}
