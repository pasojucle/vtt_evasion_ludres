<?php

namespace App\Repository;

use App\Entity\SlideshowDirectory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SlideshowDirectory>
 *
 * @method SlideshowDirectory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SlideshowDirectory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SlideshowDirectory[]    findAll()
 * @method SlideshowDirectory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SlideshowDirectoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SlideshowDirectory::class);
    }

    /**
     * @return SlideshowDirectory[] Returns an array of SlideshowDirectory objects
     */
    public function findAllASC(): array
    {
        return $this->createQueryBuilder('s')
           ->orderBy('s.name', 'ASC')
           ->getQuery()
           ->getResult()
       ;
    }
}
