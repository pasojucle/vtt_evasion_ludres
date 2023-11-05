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

    public function findOneByName($name): ?Parameter
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere(
                    (new Expr())->eq('p.name', ':name')
                )
                ->setParameter('name', $name)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findByParameterGroupName(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.parameterGroup', 'pg')
            ->andWhere(
                (new Expr())->eq('pg.name', ':name')
            )
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();
    }
}
