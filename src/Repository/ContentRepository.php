<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Content;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
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

    public function findContentQuery(?string $route = null, ?bool $isFlash = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        $andX = $qb->expr()->andX();
        if (null !== $route) {
            $andX->add($qb->expr()->eq('c.route', ':route'));
        } else {
            $andX->add($qb->expr()->neq('c.route', ':route'));
            $route = 'home';
        }

        if (null !== $isFlash) {
            $andX->add($qb->expr()->eq('c.isFlash', ':isFlash'));
            $qb->setParameter('isFlash', $isFlash);
        }

        return $qb
            ->andWhere($andX)
            ->setParameter('route', $route)
            ->orderBy('c.orderBy', 'ASC')
            ;
    }

    public function findByRoute(string $route, ?bool $isFlash = null): array
    {
        $qb = $this->findContentQuery($route, $isFlash);

        return $qb->getQuery()->getResult();
    }

    public function findNexOrderByRoute(string $route, bool $isFlash): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('c')
            ->select('MAX(c.orderBy)')
            ->andWhere(
                (new Expr())->eq('c.route', ':route'),
                (new Expr())->eq('c.isFlash', ':isFlash')
            )
            ->setParameter('route', $route)
            ->setParameter('isFlash', $isFlash)
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
                    (new Expr())->eq('l.route', ':route')
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

        $contents = $qb
            ->andWhere(
                $qb->expr()->eq('c.route', ':route'),
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
            ->setParameter('route', 'home')
            ->setParameter('today', $today->format('Y-m-d h:i:s'))
            ->orderBy('c.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
            ;
        $homeContents = [];
        if (null !== $contents) {
            foreach ($contents as $content) {
                $type = ($content->isFlash()) ? 'flashes' : 'contents';
                $homeContents[$type][] = $content;
            }
        }

        return $homeContents;
    }
}
