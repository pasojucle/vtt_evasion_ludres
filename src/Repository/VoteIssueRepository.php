<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vote;
use App\Entity\VoteIssue;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method VoteIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoteIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoteIssue[]    findAll()
 * @method VoteIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteIssue::class);
    }

    public function findBysurveyAndContent(int $surveyId, ?string $content): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->join('sr.vote', 's')
            ->andWhere(
                (new Expr())->eq('s.id', ':surveyId')
            )
            ->setParameter('surveyId', $surveyId);

        if(null !== $content) {
            $qb            
                ->andWhere(
                    (new Expr())->like('sr.content', ':content')
                )
                ->setParameter('content', '%'.$content.'%');
        }
        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function findByVote(Vote $survey): array
     {
        return $this->createQueryBuilder('sr')
            ->where(
                (new Expr)->eq('sr.vote', ':survey')
            )
            ->setParameter('survey', $survey)
            ->getQuery()
            ->getResult()
        ;
    }
}
