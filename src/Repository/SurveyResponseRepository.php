<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\Survey;
use Doctrine\ORM\Query\Expr;
use App\Entity\SurveyResponse;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
}
