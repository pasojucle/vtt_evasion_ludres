<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Section|null find($id, $lockMode = null, $lockVersion = null)
 * @method Section|null findOneBy(array $criteria, array $orderBy = null)
 * @method Section[]    findAll()
 * @method Section[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Section::class);
    }

    /**
    * @return Section[] Returns an array of Section objects
    */
    
    public function findAll()
    {
        return $this->createQueryBuilder('s')
            ->join('s.chapters', 'c')
            ->join('c.articles', 'a')
            ->orderBy('s.title', 'ASC')
            ->orderBy('c.title', 'ASC')
            ->orderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
