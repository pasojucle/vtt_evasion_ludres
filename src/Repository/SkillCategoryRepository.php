<?php

namespace App\Repository;

use App\Entity\SkillCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
