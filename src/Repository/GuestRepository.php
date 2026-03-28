<?php

namespace App\Repository;

use App\Entity\Guest;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guest>
 */
class GuestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guest::class);
    }

    public function findOneByValidToken(string $token): ?Guest
    {
        return $this->createQueryBuilder('g')
               ->andWhere(
                   (new Expr())->eq('g.token', ':token'),
                   (new Expr())->gte('g.tokenExpiresAt', ':now'),
               )
               ->setParameter('token', $token)
               ->setParameter('now', (new DateTime())->format(DateTime::ATOM))
               ->getQuery()
               ->getOneOrNullResult()
           ;
    }

    public function findOneByEmail(string $email): ?Guest
    {
        try {
            return $this->createQueryBuilder('g')
                    ->andWhere(
                        (new Expr())->eq('g.email', ':email'),
                    )
                    ->setParameter('email', $email)
                    ->getQuery()
                    ->getOneOrNullResult()
                ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
