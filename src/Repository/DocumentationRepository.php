<?php

namespace App\Repository;

use App\Entity\Documentation;
use App\Entity\Log;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Documentation>
 *
 * @method Documentation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Documentation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Documentation[]    findAll()
 * @method Documentation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Documentation::class);
    }

    public function save(Documentation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Documentation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDocumentationQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.orderBy', 'ASC')
            ->addOrderBy('d.name', 'ASC');
    }

    /**
     * @return Documentation[] Returns an array of Department objects
     */
    public function findAllAsc(): array
    {
        return $this->findDocumentationQuery()
            ->getQuery()
            ->getResult();
    }

    public function findNexOrder(): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('d')
            ->select('MAX(d.orderBy)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (null !== $maxOrder) {
            $maxOrder = (int) $maxOrder;
            $nexOrder = $maxOrder + 1;
        }

        return $nexOrder;
    }

    public function quertNoveltiesByUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('d')
            ->leftjoin(Log::class, 'log', 'WITH', (new Expr())->andX((new Expr())->eq('d.id', 'log.entityId'), (new Expr())->eq('log.entity', ':entityName'), (new Expr())->eq('log.user', ':user')))
            ->andWhere(
                (new Expr())->orX(
                    (new Expr())->isNull('log'),
                    (new Expr())->lt('log.viewAt', 'd.updateAt'),
                ),
                (new Expr())->isNotNull('d.updateAt'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('entityName', 'Documentation'),
            ]))
       ;
    }

    /**
     * @return Documentation[] Returns an array of Link objects
     */
    public function findNoveltiesByUser(User $user): array
    {
        return $this->quertNoveltiesByUser($user)
            ->getQuery()
            ->getResult()
       ;
    }

    /**
     * @return int[] Returns an array of integer
     */
    public function findNoveltiesByUserIds(User $user): array
    {
        return $this->quertNoveltiesByUser($user)
            ->select('d.id')
            ->getQuery()
            ->getSingleColumnResult()
       ;
    }
}
