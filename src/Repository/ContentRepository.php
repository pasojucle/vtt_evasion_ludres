<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Content;
use App\Entity\Enum\ContentKindEnum;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Content|null find($id, $lockMode = null, $lockVersion = null)
 * @method Content|null findOneBy(array $criteria, array $orderBy = null)
 * @method Content[]    findAll()
 * @method Content[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    public function findContentQuery(?string $route = null, ?ContentKindEnum $kind = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        $andX = $qb->expr()->andX();
        if (null !== $route) {
            $andX->add($qb->expr()->eq('c.route', ':route'));
            $andX->add($qb->expr()->isNotNull('c.parent'));
        } else {
            $andX->add($qb->expr()->neq('c.route', ':route'));
            $andX->add($qb->expr()->isNull('c.parent'));
            $route = 'home';
        }

        if (null !== $kind) {
            $andX->add($qb->expr()->eq('c.kind', ':kind'));
            $qb->setParameter('kind', $kind->value);
        }

        return $qb
            ->andWhere($andX)
            ->setParameter('route', $route)
            ->orderBy('c.orderBy', 'ASC')
            ;
    }

    public function findByRoute(string $route, ContentKindEnum $kind): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere(
                (new Expr())->eq('c.route', ':route'),
                (new Expr())->eq('c.kind', ':kind')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('route', $route),
                new Parameter('kind', $kind->value),
            ]))
            ->orderBy('c.orderBy', 'ASC')
            ->getQuery()->getResult();
    }

    public function findNexOrderByRoute(string $route, ContentKindEnum $kind): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('c')
            ->select('MAX(c.orderBy)')
            ->andWhere(
                (new Expr())->eq('c.route', ':route'),
                (new Expr())->isNotNull('c.parent'),
                (new Expr())->eq('c.kind', ':kind')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('route', $route),
                new Parameter('kind', $kind),
            ]))
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (null !== $maxOrder) {
            $maxOrder = (int) $maxOrder;
            $nexOrder = $maxOrder + 1;
        }

        return $nexOrder;
    }

    public function findOneByRoute(string $route): ?Content
    {
        try {
            return $this->createQueryBuilder('l')
                ->andWhere(
                    (new Expr())->eq('l.route', ':route'),
                    (new Expr())->isNull('l.parent'),
                )
                ->setParameter('route', $route)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findHomeContents(): array
    {
        $today = new DateTime();
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->andWhere(
                $qb->expr()->eq('c.route', ':route'),
                $qb->expr()->isNotNull('c.parent'),
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->isNull('c.startAt'),
                        $qb->expr()->isNull('c.endAt')
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->lte('c.startAt', ':today'),
                        $qb->expr()->gte('c.endAt', ':today')
                    )
                )
            )
            ->setParameters(new ArrayCollection([
                new Parameter('route', 'home'),
                new Parameter('today', $today->format('Y-m-d h:i:s'))
            ]))
            ->orderBy('c.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
