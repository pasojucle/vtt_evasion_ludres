<?php

namespace App\Repository;

use App\Entity\ModalWindow;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
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
     * @return ModalWindow[] Returns an array of FlashInfo objects
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
     * @return ModalWindow[] Returns an array of FlashInfo objects
     */
    public function findByAge(?int $age): array
    {
        $today = new DateTime();
        $qb = $this->createQueryBuilder('m')
            ->andWhere(
                (new Expr())->lte('m.startAt', ':today'),
                (new Expr())->gte('m.endAt', ':today'),
                (new Expr())->eq('m.isDisabled', ':disabled'),
                (new Expr())->eq('m.public', ':public'),
            )
            ->setParameter('today', $today->format('Y-m-d H:i:s'))
            ->setParameter('disabled', 0)
            ->setParameter('public', 0);

        if (null !== $age) {
            $qb->andWhere(
                (new Expr())->orX(
                    (new Expr())->lte('m.minAge', ':age'),
                    (new Expr())->isNull('m.minAge')
                ),
                (new Expr())->orX(
                    (new Expr())->isNull('m.maxAge'),
                    (new Expr())->gte('m.maxAge', ':age')
                ),
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

    /**
     * @return ModalWindow[] Returns an array of FlashInfo objects
     */
    public function findPublic(): array
    {
        $today = new DateTime();
        return $this->createQueryBuilder('m')
            ->andWhere(
                (new Expr())->lte('m.startAt', ':today'),
                (new Expr())->gte('m.endAt', ':today'),
                (new Expr())->eq('m.isDisabled', ':disabled'),
                (new Expr())->eq('m.public', ':public')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('today', $today->format('Y-m-d H:i:s')),
                new Parameter('disabled', 0),
                new Parameter('public', 1),
            ]))
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
