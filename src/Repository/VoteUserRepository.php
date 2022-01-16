<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vote;
use App\Entity\VoteUser;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method VoteUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoteUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoteUser[]    findAll()
 * @method VoteUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteUser::class);
    }

    public function findOneByVoteAndUser(Vote $vote, User $user): ?VoteUser
    {
        try {
            return $this->createQueryBuilder('v')
                ->andWhere(
                    (new Expr())->eq('v.vote', ':vote'),
                    (new Expr())->eq('v.user', ':user'),
                )
                ->setParameters([
                    'vote' => $vote,
                    'user' => $user,
                ])
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
