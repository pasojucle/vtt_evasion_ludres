<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Link;
use App\Entity\Log;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function findLinkQuery(int $position): QueryBuilder
    {
        return $this->createQueryBuilder('l')
            ->andWhere(
                (new Expr())->eq('l.position', ':position')
            )
            ->setParameter('position', $position)
            ->orderBy('l.orderBy', 'ASC')
            ->addOrderBy('l.title', 'ASC')
        ;
    }

    /**
     * @return Link[] Returns an array of link objects
     */
    public function findByPosition(int $position): array
    {
        $qb = $this->findLinkQuery($position);

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNexOrderByPosition(int $position): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('l')
            ->select('MAX(l.orderBy)')
            ->andWhere(
                (new Expr())->eq('l.position', ':position')
            )
            ->setParameter('position', $position)
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
        return $this->createQueryBuilder('l')
            ->leftjoin(Log::class, 'log', 'WITH', (new Expr())->andX((new Expr())->eq('l.id', 'log.entityId'), (new Expr())->eq('log.entity', ':entityName'), (new Expr())->eq('log.user', ':user')))
            ->andWhere(
                (new Expr())->orX(
                    (new Expr())->isNull('log'),
                    (new Expr())->lt('log.viewAt', 'l.updateAt'),
                ),
                (new Expr())->eq('l.position', ':position'),
                (new Expr())->isNotNull('l.updateAt'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('entityName', 'Link'),
                new Parameter('position', Link::POSITION_LINK_PAGE),
            ]))
       ;
    }

    /**
     * @return Link[] Returns an array of Link objects
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
            ->select('l.id')
            ->getQuery()
            ->getSingleColumnResult()
       ;
    }
}
