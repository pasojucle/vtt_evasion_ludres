<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\Survey;
use App\Entity\SurveyUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SurveyUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveyUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveyUser[]    findAll()
 * @method SurveyUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyUser::class);
    }

    public function findOneBySurveyAndUser(Survey $survey, User $user): ?SurveyUser
    {
        try {
            return $this->createQueryBuilder('v')
                ->andWhere(
                    (new Expr())->eq('v.survey', ':survey'),
                    (new Expr())->eq('v.user', ':user'),
                )
                ->setParameters([
                    'survey' => $survey,
                    'user' => $user,
                ])
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findActiveSurveysByUser(User $user): array
    {
        $userSurveys = $this->createQueryBuilder('vu')
            ->join('vu.survey', 'v')
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
        $surveys = [];
        $surveysCreatedAt = [];
        if (!empty($userSurveys)) {
            foreach ($userSurveys as $userSurvey) {
                $survey = $userSurvey->getSurvey();
                $surveys[] = $survey;
                $surveysCreatedAt[$survey->getId()] = $userSurvey->getCreatedAt();
            }
        }

        return [
            'surveys' => $surveys,
            'surveysCreatedAt' => $surveysCreatedAt,
        ];
    }
}
