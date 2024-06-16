<?php

namespace App\Repository;

use App\Entity\Notification;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[] Returns an array of FlashInfo objects
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
     * @return Notification[] Returns an array of FlashInfo objects
     */
    public function findByAge(?int $age): array
    {
        $today = new DateTime();
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->lte('m.startAt', ':today'));
        $andX->add((new Expr())->gte('m.endAt', ':today'));
        $andX->add((new Expr())->eq('m.isDisabled', ':disabled'));
        $parameters = [
            new Parameter('today', $today->format('Y-m-d H:i:s')),
            new Parameter('disabled', false),
        ];

        if (null !== $age) {
            $minOrX = (new Expr())->orX();
            $minOrX->add((new Expr())->lte('m.minAge', ':age'));
            $minOrX->add((new Expr())->isNull('m.minAge'));
            $andX->add($minOrX);
            $maxOrX = (new Expr())->orX();
            $maxOrX->add((new Expr())->isNull('m.maxAge'));
            $maxOrX->add((new Expr())->gte('m.maxAge', ':age'));
            $andX->add($maxOrX);
            $parameters[] = new Parameter('age', $age);
        }

        return $this->createQueryBuilder('m')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Notification[] Returns an array of FlashInfo objects
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
