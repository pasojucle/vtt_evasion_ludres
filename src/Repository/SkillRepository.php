<?php

namespace App\Repository;

use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        return $this->createQueryBuilder('s')
            ->join('s.category', 'c')
           ->orderBy('c.name', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }
}
