<?php

namespace App\Repository;

use App\Entity\DiseaseKind;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiseaseKind>
 *
 * @method DiseaseKind|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiseaseKind|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiseaseKind[]    findAll()
 * @method DiseaseKind[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiseaseKindRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiseaseKind::class);
    }

    public function save(DiseaseKind $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiseaseKind $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return DiseaseKind[] Returns an array of DiseaseKind objects
     */
    public function findAllOrderByCategory(int $licenceCategory): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere(
                (new Expr())->eq('d.deleted', ':deleted'),
                (new Expr())->orX(
                    (new Expr())->eq('d.licenceCategory', ':licenceCategory'),
                    (new Expr())->isNull('d.licenceCategory'),
                ),
            )
            ->setParameters([
                'deleted' => 0,
                'licenceCategory' => $licenceCategory,
            ])
           ->orderBy('d.category', 'ASC')
           ->addOrderBy('d.orderBy', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }

    public function findDiseaseKindQuery(int $category): QueryBuilder
    {
        return $this->createQueryBuilder('d')
        ->andWhere(
            (new Expr())->eq('d.category', ':category'),
            (new Expr())->eq('d.deleted', ':deleted'),
        )
        ->setParameter('category', $category)
        ->setParameter('deleted', 0)
        ->orderBy('d.orderBy', 'ASC')
        ;
    }

    /**
     * @return DiseaseKind[] Returns an array of Level objects
     */
    public function findByCategory(int $category): array
    {
        $qb = $this->findDiseaseKindQuery($category);

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNexOrderByCategory(int $category): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('d')
            ->select('MAX(d.orderBy)')
            ->andWhere(
                (new Expr())->eq('d.category', ':category'),
                (new Expr())->eq('d.deleted', ':deleted'),
            )
            ->setParameter('category', $category)
            ->setParameter('deleted', 0)
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
