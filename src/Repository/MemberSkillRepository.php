<?php

namespace App\Repository;

use App\Entity\Cluster;
use App\Entity\Enum\EvaluationEnum;
use App\Entity\Level;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MemberSkill>
 */
class MemberSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberSkill::class);
    }

    /**
     * @return MemberSkill[] Returns an array of MemberSkill objects
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
     * @return MemberSkill[] Returns an array of MemberSkill objects
     */
    public function findByUsers(array $members): array
    {
        return $this->createQueryBuilder('us')
            ->join('us.user', 'u')
            ->join('us.skill', 's')
            ->join('s.category', 'c')
            ->andWhere(
                (new Expr())->in('u.id', ':members'),
            )
            ->setParameter('members', $members)
            ->orderBy('u.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return MemberSkill[] Returns an array of MemberSkill objects
     */
    public function findByMember(Member $member, ?SkillCategory $category, ?Level $level): array
    {
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('us.member', ':member'));
        $parameters = [new Parameter('member', $member)];
        if (null !== $category) {
            $andX->add((new Expr())->eq('s.category', ':category'));
            $parameters[] = new Parameter('category', $category);
        }
        if (null !== $level) {
            $andX->add((new Expr())->eq('s.level', ':level'));
            $parameters[] = new Parameter('level', $level);
        }

        return $this->createQueryBuilder('us')
            ->join('us.skill', 's')
            ->join('s.category', 'c')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * @return MemberSkill[] Returns an array of MemberSkill objects
     */
    public function findNotViewedByUser(Member $member): array
    {
        $viewed = $this->getEntityManager()->createQueryBuilder()
            ->select('log.entityId')
            ->from(Log::class, 'log')
            ->andWhere(
                (new Expr())->eq('log.member', ':member'),
                (new Expr())->eq('log.entity', ':entityName')
            );

        return $this->createQueryBuilder('us')
            ->andWhere(
                (new Expr())->notIn('us.id', $viewed->getDQL()),
                (new Expr())->in('us.member', ':member')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('member', $member),
                new Parameter('entityName', 'MemberSkill'),
            ]))
            ->getQuery()
            ->getResult()
       ;
    }

    public function getMemberSkillQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('ms')
            ->join('ms.skill', 'sk')->addSelect('sk');
    }

    public function filterEvaluation(QueryBuilder $qb, EvaluationEnum $evaluation): void
    {
        $qb->andWhere(
            $qb->expr()->eq('ms.evaluation', ':evaluation')
        )
        ->setParameter('evaluation', $evaluation);
    }

    public function filterCategory(QueryBuilder $qb, SkillCategory $category): void
    {
        $qb->andWhere(
            $qb->expr()->eq('sk.category', ':category')
        )
        ->setParameter('category', $category);
    }

    public function filterLevel(QueryBuilder $qb, Level $level): void
    {
        $qb->andWhere(
            $qb->expr()->eq('sk.level', ':level')
        )
        ->setParameter('level', $level);
    }

    public function filterUser(QueryBuilder $qb, Member $member): void
    {
        $qb->andWhere(
            $qb->expr()->eq('ms.member', ':member')
        )
        ->setParameter('member', $member);
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('sk.content', $direction);
    }

    public function getTotalByCategoryByMember(Member $member): array
    {
        return $this->createQueryBuilder('msk')
            ->select(
                'ca.id',
                'ca.name',
                'COUNT(msk.id) AS total',
                'MAX(le.orderBy) AS maxLevel'
            )
            ->join('msk.skill', 'sk')
            ->join('sk.category', 'ca')
            ->join('sk.level', 'le')
            ->andWhere('msk.member = :member')
            ->andWhere('msk.evaluation = :acquired')
            ->setParameter('member', $member)
            ->setParameter('acquired', EvaluationEnum::ACQUIRED)
            ->groupBy('ca.id', 'ca.name')
            ->getQuery()
            ->getResult();
    }
}
