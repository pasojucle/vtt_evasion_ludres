<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Parameter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Parameter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parameter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parameter[]    findAll()
 * @method Parameter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParameterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parameter::class);
    }

    public function findOneById(string $id): ?Parameter
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere(
                    (new Expr())->eq('p.id', ':id')
                )
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere(
                (new Expr())->in('p.id', ':ids')
            )
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findBySectionId(string $id): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.section', 's')
            ->andWhere(
                (new Expr())->eq('s.id', ':id')
            )
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
