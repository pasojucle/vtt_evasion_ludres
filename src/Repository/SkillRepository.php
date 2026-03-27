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
    public function findAllOrdered(): array
    {
        dump("findAllOrdered");
        return $this->createQueryBuilder('s')
            ->join('s.category', 'c')
           ->orderBy('c.name', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }

    /**
     * @return Skill[] Returns an array of Skill objects
     */
    public function findFiltered(?string $category, ?string $level): array
    {
        dump("findFiltered");
        $andX = (new Expr())->andX();
        $parameters = [];
        if (null !== $category) {
            dump($category);
            $andX->add((new Expr)->eq('s.category', ':category'));
            $parameters[] = new Parameter('category', (int) $category);
        }
        if (null !== $level) {
            dump($level);
            $andX->add((new Expr)->eq('s.level', ':level'));
            $parameters[] = new Parameter('level', (int) $level);
        }

        return $this->createQueryBuilder('s')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
       ;
    }
}
