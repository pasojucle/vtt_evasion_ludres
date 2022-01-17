<?php

namespace App\Repository;

use App\Entity\Vote;
use App\Entity\VoteResponse;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
        $responses = $this->createQueryBuilder('r')
            ->join('r.voteIssue', 'i')
            ->andWhere(
                (new Expr())->eq('i.vote', ':vote')
            )
            ->setParameter('vote', $vote)
            ->getQuery()
            ->getResult()
        ;

        $responsedByUuid = [];
        if (!empty($responses)) {
            foreach ($responses as $response) {
                $responsedByUuid[$response->getUuid()]['responses'][] = $response;
            }
        }

        return $responsedByUuid;
    }
}
