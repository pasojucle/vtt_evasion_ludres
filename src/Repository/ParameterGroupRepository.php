<?php

namespace App\Repository;

use Doctrine\ORM\Query\Expr;
use App\Entity\ParameterGroup;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ParameterGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParameterGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParameterGroup[]    findAll()
 * @method ParameterGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParameterGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private Security $security)
    {
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
}