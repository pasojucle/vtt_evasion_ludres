<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Respondent;
use App\Entity\Survey;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Respondent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Respondent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Respondent[]    findAll()
 * @method Respondent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RespondentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Respondent::class);
    }

    public function findOneBySurveyAndUser(Survey $survey, User $user): ?Respondent
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
        return  $this->createQueryBuilder('vu')
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
    }

    
    public function deleteResponsesByUserAndSurvey(User $user, Survey $survey): void
    {
        $this->createQueryBuilder('r')
        ->delete()
        ->andWhere(
            (new Expr())->eq('r.user', ':user'),
            (new Expr())->in('r.survey', ':survey')
        )
        ->setParameters([
            'user' => $user,
            'survey' => $survey,
        ])
        ->getQuery()
        ->getResult()
    ;
    }
}
