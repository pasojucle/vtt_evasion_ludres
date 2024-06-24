<?php

namespace App\Repository;

use DateTime;
use App\Entity\Log;
use App\Dto\UserDto;
use App\Entity\User;
use App\Entity\Notification;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
        return $this->createQueryBuilder('n')
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Notification[] Returns an array of FlashInfo objects
     */
    public function findByUser(User $user, int $age): array
    {        
        $viewed = $this->getEntityManager()->createQueryBuilder()
        ->select('log.entityId')
        ->from(Log::class, 'log')
        ->andWhere(
            (new Expr())->eq('log.user', ':user'),
            (new Expr())->eq('log.entity', ':entityName')
        );

        $today = new DateTime();
         return $this->createQueryBuilder('n')
            ->andWhere(
                (new Expr())->lte('n.startAt', ':today'),
                (new Expr())->gte('n.endAt', ':today'),
                (new Expr())->eq('n.isDisabled', ':disabled'),
                (new Expr())->eq('n.public', ':public'),
                (new Expr())->orX(
                    (new Expr())->lte('n.minAge', ':age'),
                    (new Expr())->isNull('n.minAge')
                ),
                (new Expr())->orX(
                    (new Expr())->isNull('n.maxAge'),
                    (new Expr())->gte('n.maxAge', ':age')
                ),
                (new Expr())->notIn('n.id', $viewed->getDQL())
            )
            ->setParameters(new ArrayCollection([
                new Parameter('today', $today->format('Y-m-d H:i:s')),
                new Parameter('disabled', false),
                new Parameter('public', false),
                new Parameter('age', $age),
                new Parameter('user', $user),
                new Parameter('entityName', 'Notification')
            ]))
            ->orderBy('n.id', 'DESC')
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
        return $this->createQueryBuilder('n')
            ->andWhere(
                (new Expr())->lte('n.startAt', ':today'),
                (new Expr())->gte('n.endAt', ':today'),
                (new Expr())->eq('n.isDisabled', ':disabled'),
                (new Expr())->eq('n.public', ':public')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('today', $today->format('Y-m-d H:i:s')),
                new Parameter('disabled', 0),
                new Parameter('public', 1),
            ]))
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
