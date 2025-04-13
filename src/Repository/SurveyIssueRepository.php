<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SurveyIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveyIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveyIssue[]    findAll()
 * @method SurveyIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyIssue::class);
    }

    public function findBysurveyAndContent(int $surveyId, ?string $content): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->join('sr.survey', 's')
            ->andWhere(
                (new Expr())->eq('s.id', ':surveyId')
            )
            ->setParameter('surveyId', $surveyId);

        if (null !== $content) {
            $qb
                ->andWhere(
                    (new Expr())->like('sr.content', ':content')
                )
                ->setParameter('content', '%' . $content . '%');
        }

        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function findBySurvey(Survey $survey): array
    {
        return $this->createQueryBuilder('sr')
            ->where(
                (new Expr())->eq('sr.survey', ':survey')
            )
            ->setParameter('survey', $survey)
            ->getQuery()
            ->getResult()
        ;
    }

    public function deleteBySurvey(Survey $survey): void
    {
        $this->createQueryBuilder('si')
        ->delete()
        ->andWhere(
            (new Expr())->in('si.survey', ':survey')
        )
        ->setParameters(new ArrayCollection([
            new Parameter('survey', $survey),
        ]))
        ->getQuery()
        ->getResult()
        ;
    }
}
