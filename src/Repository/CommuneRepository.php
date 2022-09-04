<?php

namespace App\Repository;

use App\Entity\Commune;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commune>
 *
 * @method Commune|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commune|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commune[]    findAll()
 * @method Commune[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommuneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commune::class);
    }

    public function add(Commune $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Commune $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteAll()
    {
        return $this->createQueryBuilder('c')
            ->delete()
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    /**
     * @return Commune[] Returns an array of Commune objects
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Commune[] Returns an array of Commune objects
     */
    public function findByCodes(array $codes): array
    {
        return $this->createQueryBuilder('c')
            ->where(
                (new Expr())->in('c.id', ':codes')
            )
            ->setParameter('codes', $codes)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

   public function findCount(): int
   {
       return $this->createQueryBuilder('c')
            ->select((new Expr)->count('c.id'))
           ->getQuery()
           ->getSingleScalarResult();
       ;
   }
}
