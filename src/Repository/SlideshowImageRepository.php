<?php

namespace App\Repository;

use App\Entity\SlideshowDirectory;
use App\Entity\SlideshowImage;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SlideshowImage>
 *
 * @method SlideshowImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method SlideshowImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method SlideshowImage[]    findAll()
 * @method SlideshowImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SlideshowImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SlideshowImage::class);
    }

    /**
     * @return SlideshowImage[] Returns an array of SlideshowImage objects
     */
    public function findRoot(): array
    {
        return $this->createQueryBuilder('i')
           ->andWhere(
               (new Expr())->isNull('i.directory')
           )
           ->getQuery()
           ->getResult()
       ;
    }

    /**
     * @return SlideshowImage[] Returns an array of SlideshowImage objects
     */
    public function findOutOfPeriod(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere(
                (new Expr())->lt('i.createdAt', ':deadline')
            )
            ->setParameter('deadline', (new DateTime())->sub(new DateInterval('P1Y')))
            ->getQuery()
            ->getResult()
       ;
    }

    /**
     * @return SlideshowImage[] Returns an array of SlideshowImage objects
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.directory', 'd')
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult()
       ;
    }
}
