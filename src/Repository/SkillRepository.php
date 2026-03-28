<?php

namespace App\Repository;

use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
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
}
