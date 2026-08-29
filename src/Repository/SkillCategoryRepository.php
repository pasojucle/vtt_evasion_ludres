<?php

namespace App\Repository;

use App\Entity\Enum\EvaluationEnum;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\SkillCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SkillCategory>
 */
class SkillCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SkillCategory::class);
    }

    /**
     * @return SkillCategory[] Returns an array of SkillCategory objects
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('sc')
               ->orderBy('sc.name', 'ASC')
               ->getQuery()
               ->getResult()
           ;
    }

    public function getSkillCategoryQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('skc');
    }

    public function filterName(QueryBuilder $qb, string $term): void
    {
        $qb
            ->andWhere(
                $qb->expr()->like('LOWER(skc.name)', ':term')
            )
            ->setParameter('term', '%' . strtolower($term) . '%');
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('skc.name', $direction);
    }

    public function filterActive(QueryBuilder $qb): void
    {
        $qb->andWhere(
            $qb->expr()->isNull('skc.deletedAt')
        );
    }

    public function getTotalSkillAcquiredByMemberAndCategory(Member $member): array
    {
        return $this->createQueryBuilder('ca')
            ->select(
                'ca.id',
                'ca.name',
                'SUM(CASE WHEN msk.member=:member AND msk.evaluation = :acquired THEN 1 ELSE 0 END) AS totalAcquired',
                'SUM(CASE WHEN msk.member=:member THEN 1 ELSE 0 END) AS total'
            )
            ->leftJoin('ca.skills', 'sk')
            ->leftJoin('sk.memberSkills', 'msk')
            ->setParameter('member', $member)
            ->setParameter('acquired', EvaluationEnum::ACQUIRED)
            ->groupBy('ca.id', 'ca.name')
            ->orderBy('ca.name')
            ->getQuery()
            ->getResult();
    }
}
