<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use App\Entity\VoteResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VoteResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoteResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoteResponse[]    findAll()
 * @method VoteResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteResponse::class);
    }

    /**
     * @return VoteResponse[] Returns an array of VoteResponse objects
     */
    public function findResponsesByUuid(Vote $vote): array
    {
        $responses = $this->findResponsesByVote($vote);

        $responsedByUuid = [];
        if (!empty($responses)) {
            foreach ($responses as $response) {
                $responsedByUuid[$response->getUuid()]['responses'][] = $response;
            }
        }

        return $responsedByUuid;
    }

    public function findResponsesByIssues(Vote $vote): array
    {
        $responses = $this->findResponsesByVote($vote);

        $responsedByIssue = [];
        if (!empty($responses)) {
            foreach ($responses as $response) {
                $responsedByIssue[$response->getVoteIssue()->getId()][] = $response;
            }
        }

        return $responsedByIssue;
    }

    public function findResponsesByVote(Vote $vote): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.voteIssue', 'i')
            ->andWhere(
                (new Expr())->eq('i.vote', ':vote')
            )
            ->setParameter('vote', $vote)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByFilter(array $filter): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere(
                (new Expr())->eq('r.voteIssue', ':voteIssue')
            )
            ->setParameter('voteIssue', $filter['issue']);

        if(isset($filter['value'])) {
            $qb            
                ->andWhere(
                    (new Expr())->eq('r.value', ':value')
                )
                ->setParameter('value', $filter['value']);
        }
        return $qb->getQuery()
            ->getResult()
        ;
    }
}
