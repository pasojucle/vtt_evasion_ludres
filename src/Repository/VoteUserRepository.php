<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vote;
use App\Entity\VoteUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findActiveVotesByUser(User $user): array
    {
        $userVotes = $this->createQueryBuilder('vu')
            ->join('vu.vote', 'v')
            ->andWhere(
                (new Expr())->eq('vu.user', ':user'),
                (new Expr())->eq('v.disabled', 0),
                (new Expr())->lte('v.startAt', 'CURRENT_DATE()'),
                (new Expr())->gte('v.endAt', 'CURRENT_DATE()'),
            )
            ->setParameters([
                'user' => $user,
            ])
            ->getQuery()
            ->getResult()
        ;
        $votes = [];
        $votesCreatedAt = [];
        if (!empty($userVotes)) {
            foreach ($userVotes as $userVote) {
                $vote = $userVote->getVote();
                $votes[] = $vote;
                $votesCreatedAt[$vote->getId()] = $userVote->getCreatedAt();
            }
        }

        return [
            'votes' => $votes,
            'votesCreatedAt' => $votesCreatedAt,
        ];
    }
}
