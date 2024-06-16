<?php

namespace App\Repository;

use App\Entity\Log;
use App\Entity\SlideshowDirectory;
use App\Entity\SlideshowImage;
use App\Entity\User;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
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

    /**
     * @return SlideshowImage[] Returns an array of SlideshowImage objects
     */
    public function findGreaterThanDate(DateTimeImmutable $viewAt): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.directory', 'd')
            ->andWhere(
                (new Expr())->gt('i.createdAt', ':viewAt')
            )
            ->setParameter('viewAt', $viewAt)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult()
       ;
    }

    /**
     * @return SlideshowImage[] Returns an array of SlideshowImage objects
     */
    public function findNotViewedByUser(User $user): array
    {
        $viewed = $this->getEntityManager()->createQueryBuilder()
            ->select('log.entityId')
            ->from(Log::class, 'log')
            ->andWhere(
                (new Expr())->eq('log.user', ':user'),
                (new Expr())->eq('log.entity', ':entityName')
            );

        return $this->createQueryBuilder('i')
            ->join('i.directory', 'd')
            ->andWhere(
                (new Expr())->notIn('i.id', $viewed->getDQL())
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('entityName', 'SlideshowImage')
            ]))
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult()
       ;
    }

    public function findOneByFilename(string $filename): ?SlideshowImage
    {
        try {
            return $this->createQueryBuilder('i')
                ->andWhere(
                    (new Expr())->eq('i.filename', ':filename')
                )
                ->setParameter('filename', $filename)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
