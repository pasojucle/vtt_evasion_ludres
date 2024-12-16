<?php

namespace App\Repository;

use App\Entity\Cluster;
use App\Entity\Log;
use App\Entity\Skill;
use App\Entity\User;
use App\Entity\UserSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSkill>
 */
class UserSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSkill::class);
    }

    /**
     * @return UserSkill[] Returns an array of UserSkill objects
     */
    public function findByClusterAndSkill(Cluster $cluster, Skill $skill): array
    {
        return $this->createQueryBuilder('us')
                ->join('us.skill', 's')
                ->join('s.clusters', 'c')
                ->andWhere(
                    (new Expr())->eq('c', ':cluster'),
                    (new Expr())->eq('us.skill', ':skill'),
                )
               ->setParameters(new ArrayCollection([
                    new Parameter('cluster', $cluster),
                    new Parameter('skill', $skill),
                ]))
               ->getQuery()
               ->getResult()
           ;
    }

    /**
     * @return UserSkill[] Returns an array of UserSkill objects
     */
    public function findByUsers(array $users): array
    {
        return $this->createQueryBuilder('us')
            ->join('us.user', 'u')
            ->join('us.skill', 's')
            ->join('s.category', 'c')
            ->andWhere(
                (new Expr())->in('u.id', ':users'),
            )
            ->setParameter('users', $users)
            ->orderBy('u.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * @return UserSkill[] Returns an array of UserSkill objects
     */
    public function findNotViewedByUser(User $user): array
    {
        $viewed = $this->getEntityManager()->createQueryBuilder()
            ->select('log.entityId')
            ->from(Log::class, 'log')
            ->andWhere(
                (new Expr())->eq('log.user', ':user'),
                (new Expr())->eq('log.entity', ':entityName')
            );

        return $this->createQueryBuilder('us')
            ->andWhere(
                (new Expr())->notIn('us.id', $viewed->getDQL()),
                (new Expr())->in('us.user', ':user')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('entityName', 'UserSkill'),
            ]))
            ->getQuery()
            ->getResult()
       ;
    }
}
