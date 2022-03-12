<?php

namespace App\Repository;

use App\Entity\ModalWindow;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ModalWindow|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModalWindow|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModalWindow[]    findAll()
 * @method ModalWindow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModalWindowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModalWindow::class);
    }

    /**
     * @return FlashInfo[] Returns an array of FlashInfo objects
     */
    public function findAllDesc(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return FlashInfo[] Returns an array of FlashInfo objects
     */
    public function findLastByAge(?int $age): array
    {
        $today = new DateTime();
        $qb = $this->createQueryBuilder('m')
            ->andWhere(
                (new Expr())->lte('m.startAt', ':today'),
                (new Expr())->gte('m.endAt', ':today'),
            )
            ->setParameter('today', $today->format('Y-m-d H:i:s'));
        if (null !== $age) {
            $qb->andWhere(
                (new Expr())->lte('m.minAge', ':age'),
                (new Expr())->gte('m.maxAge', ':age')
            )
                ->setParameter('age', $age)
                ;
        }

        return $qb
                ->orderBy('m.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult()
        ;
    }
}
