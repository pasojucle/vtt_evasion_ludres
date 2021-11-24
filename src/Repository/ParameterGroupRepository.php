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
        if ($this->security->isGranted('ROLE_SUPER_USER')) {
            $qb->andWhere(
                (new Expr())->eq('p.role', ':roleSuperAdmin')
            )
            ->setParameter('roleSuperAdmin', 'ROLE_SUPER_ADMIN');
        }

        return $qb->andWhere(
                (new Expr())->eq('p.role', ':roleAdmin')
            )
            ->setParameter('roleAdmin', 'ROLE_ADMIN')
            ->orderBy('p.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
