<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ParameterGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @method ParameterGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParameterGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParameterGroup[]    findAll()
 * @method ParameterGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParameterGroupRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private Security $security
    ) {
        parent::__construct($registry, ParameterGroup::class);
    }

    /**
     * @return ParameterGroup[] Returns an array of ParameterGroup objects
     */
    public function findParameterGroups(): array
    {
        $qb = $this->createQueryBuilder('p');
        $roles = ['ROLE_ADMIN'];
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }

        return $qb
            ->andWhere(
                (new Expr())->in('p.role', ':roles'),
            )
            ->setParameter('roles', $roles)
            ->orderBy('p.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByName(string $name): ?ParameterGroup
    {
        try {
            return $this->createQueryBuilder('pG')
                ->andWhere(
                    (new Expr())->eq('pG.name', ':name')
                )
                ->setParameter('name', $name)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
