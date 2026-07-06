<?php

namespace App\Repository;

use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skill>
 */
class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    /**
     * @return Skill[] Returns an array of Skill objects
     */
    public function findFiltered(?int $category, ?int $level, ?int $cluster = null): array
    {
        $andX = (new Expr())->andX();
        $parameters = [];
        if (null !== $category) {
            $andX->add((new Expr())->eq('s.category', ':category'));
            $parameters[] = new Parameter('category', $category);
        }
        if (null !== $level) {
            $andX->add((new Expr())->eq('s.level', ':level'));
            $parameters[] = new Parameter('level', $level);
        }
        if (null !== $cluster) {
            $clusterSkills = $this->createQueryBuilder('cs')
            ->select('cs.id')
            ->join('s.clusters', 'c')
            ->andWhere(
                (new Expr())->eq('c.id', ':cluster')
            );

            $andX->add((new Expr())->notIn('s.id', $clusterSkills->getDQL()));
            $parameters[] = new Parameter('cluster', $cluster);
        }

        $qb = $this->createQueryBuilder('s');
        if (0 < $andX->count()) {
            $qb
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters));
        }
        return $qb
            ->orderBy('s.content', 'ASC')
            ->getQuery()
            ->getResult()
       ;
    }

    public function getSkillQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('sk');
    }

    public function filterCategory(QueryBuilder $qb, SkillCategory $category): void
    {
        $qb->andWhere(
            $qb->expr()->eq('sk.category', ':category')
        )
        ->setParameter('category', $category);
    }

    public function filterContent(QueryBuilder $qb, string $term): void
    {
        $orX = $qb->expr()->orX();
        foreach (explode(' ', $term) as $key => $word) {
            $orX->add(
                $qb->expr()->like('sk.content', ':word_' . $key)
            );
            $qb->setParameter('word_' . $key, '%' . $word . '%');
        }

        $qb->andWhere($orX);
    }

    public function filterLevel(QueryBuilder $qb, Level $level): void
    {
        $qb->andWhere(
            $qb->expr()->eq('sk.level', ':level')
        )
        ->setParameter('level', $level);
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('sk.content', $direction);
    }

    public function filterActive(QueryBuilder $qb): void
    {
        $qb->andWhere(
            $qb->expr()->isNull('sk.deletedAt')
        );
    }
}
