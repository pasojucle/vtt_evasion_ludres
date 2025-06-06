<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SurveyResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveyResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveyResponse[]    findAll()
 * @method SurveyResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyResponse::class);
    }

    public function findResponsesByUuid(Survey $survey): array
    {
        $responses = $this->findResponsesBySurvey($survey);

        $responsedByUuid = [];
        if (!empty($responses)) {
            foreach ($responses as $response) {
                $responsedByUuid[$response->getUuid()]['responses'][] = $response;
            }
        }

        return $responsedByUuid;
    }


    public function findResponsesByIssues(Survey $survey): array
    {
        $responses = $this->findResponsesBySurvey($survey);

        $responsedByIssue = [];
        if (!empty($responses)) {
            foreach ($responses as $response) {
                $responsedByIssue[$response->getSurveyIssue()->getId()][] = $response;
            }
        }

        return $responsedByIssue;
    }

    /**
     * @return SurveyResponse[] Returns an array of SurveyResponse objects
     */    public function findResponsesBySurvey(Survey $survey): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.surveyIssue', 'i')
            ->andWhere(
                (new Expr())->eq('i.survey', ':survey')
            )
            ->setParameter('survey', $survey)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByFilter(array $filter): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere(
                (new Expr())->eq('r.surveyIssue', ':surveyIssue')
            )
            ->setParameter('surveyIssue', $filter['issue']);

        if (isset($filter['value'])) {
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

    public function deleteResponsesByUser(User $user): void
    {
        $this->createQueryBuilder('r')
        ->delete()
        ->andWhere(
            (new Expr())->eq('r.user', ':user')
        )
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult()
    ;
    }

    public function deleteBySurvey(Survey $survey): void
    {
        $responses = $this->createQueryBuilder('response')
        ->select('(response)')
        ->join('response.surveyIssue', 'si')
        ->where(
            (new Expr())->eq('si.survey', ':survey')
        );

        $this->createQueryBuilder('r')
        ->delete()
        ->andWhere(
            (new Expr())->in('r', $responses->getDql())
        )
        ->setParameter('survey', $survey)
        ->getQuery()
        ->getResult()
    ;
    }

    public function deleteResponsesByUserAndSurvey(User $user, Survey $survey): void
    {
        $issues = $this->getEntityManager()->createQueryBuilder()
        ->select('(issue)')
        ->from(SurveyIssue::class, 'issue')
        ->where(
            (new Expr())->eq('issue.survey', ':survey')
        );

        $this->createQueryBuilder('r')
        ->delete()
        ->andWhere(
            (new Expr())->eq('r.user', ':user'),
            (new Expr())->in('r.surveyIssue', $issues->getDQL())
        )
        ->setParameters(new ArrayCollection([
            new Parameter('user', $user),
            new Parameter('survey', $survey),
        ]))
        ->getQuery()
        ->getResult()
    ;
    }

    public function findResponsesByUserAndSurvey(User $user, Survey $survey): array
    {
        $issues = $this->getEntityManager()->createQueryBuilder()
        ->select('(issue)')
        ->from(SurveyIssue::class, 'issue')
        ->where(
            (new Expr())->eq('issue.survey', ':survey')
        );

        return $this->createQueryBuilder('r')
        ->andWhere(
            (new Expr())->eq('r.user', ':user'),
            (new Expr())->in('r.surveyIssue', $issues->getDQL())
        )
        ->setParameters(new ArrayCollection([
            new Parameter('user', $user),
            new Parameter('survey', $survey),
        ]))
        ->getQuery()
        ->getResult()
    ;
    }
}
