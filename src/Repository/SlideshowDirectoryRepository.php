<?php

namespace App\Repository;

use App\Entity\SlideshowDirectory;
use App\Repository\Interface\SlideshowDirectoryRepositoryInterface;
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
class SlideshowDirectoryRepository extends ServiceEntityRepository implements SlideshowDirectoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SlideshowDirectory::class);
    }

    public function save(SlideshowDirectory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SlideshowDirectory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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
