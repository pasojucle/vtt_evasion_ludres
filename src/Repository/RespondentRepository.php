<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Respondent;
use App\Entity\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
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

    public function findOneBySurveyAndUser(Survey $survey, Member $member): ?Respondent
    {
        try {
            return $this->createQueryBuilder('v')
                ->andWhere(
                    (new Expr())->eq('v.survey', ':survey'),
                    (new Expr())->eq('v.member', ':member'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('survey', $survey),
                    new Parameter('member', $member),
                ]))
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findActiveSurveysByUser(Member $member): array
    {
        return  $this->createQueryBuilder('vu')
            ->join('vu.survey', 'v')
            ->andWhere(
                (new Expr())->eq('vu.member', ':member'),
                (new Expr())->eq('v.disabled', 0),
                (new Expr())->lte('v.startAt', 'CURRENT_DATE()'),
                (new Expr())->gte('v.endAt', 'CURRENT_DATE()'),
            )
            ->setParameter('member', $member)
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function deleteResponsesByUserAndSurvey(Member $member, Survey $survey): void
    {
        $this->createQueryBuilder('r')
        ->delete()
        ->andWhere(
            (new Expr())->eq('r.member', ':member'),
            (new Expr())->in('r.survey', ':survey')
        )
        ->setParameters(new ArrayCollection([
            new Parameter('member', $member),
            new Parameter('survey', $survey),
        ]))
        ->getQuery()
        ->getResult()
        ;
    }
    
    public function deleteBySurvey(Survey $survey): void
    {
        $this->createQueryBuilder('r')
        ->delete()
        ->andWhere(
            (new Expr())->in('r.survey', ':survey')
        )
        ->setParameters(new ArrayCollection([
            new Parameter('survey', $survey),
        ]))
        ->getQuery()
        ->getResult()
        ;
    }

    public function findBySurvey(Survey $survey): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere(
                (new Expr())->eq('v.survey', ':survey'),
            )
            ->setParameter('survey', $survey)
            ->getQuery()
            ->getResult()
        ;
    }
}
