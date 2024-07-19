<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\History;
use App\Entity\Log;
use App\Entity\Respondent;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Survey|null find($id, $lockMode = null, $lockVersion = null)
 * @method Survey|null findOneBy(array $criteria, array $orderBy = null)
 * @method Survey[]    findAll()
 * @method Survey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Survey::class);
    }

    public function findAllDESCQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC')
        ;
    }

    public function findActive(User $member): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.members', 'm')
            ->andWhere(
                (new Expr())->eq('s.disabled', 0),
                (new Expr())->lte('s.startAt', 'CURRENT_DATE()'),
                (new Expr())->gte('s.endAt', 'CURRENT_DATE()'),
                (new Expr())->isNull('s.bikeRide'),
                (new Expr())->orX(
                    (new Expr())->isNull('m'),
                    (new Expr())->eq('m', ':member'),
                ),
            )
            ->setParameter('member', $member)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findActiveAndWithoutResponse(User $member): array
    {
        $respondents = $this->getEntityManager()->createQueryBuilder()
            ->select('(r.survey)')
            ->from(Respondent::class, 'r')
            ->where(
                (new Expr())->eq('r.user', ':rMember')
            );

        return $this->createQueryBuilder('s')
            ->leftJoin('s.members', 'm')
            ->andWhere(
                (new Expr())->eq('s.disabled', 0),
                (new Expr())->lte('s.startAt', 'CURRENT_DATE()'),
                (new Expr())->gte('s.endAt', 'CURRENT_DATE()'),
                (new Expr())->isNull('s.bikeRide'),
                (new Expr())->notIn('s', $respondents->getDQL()),
                (new Expr())->orX(
                    (new Expr())->isNull('m'),
                    (new Expr())->eq('m', ':member'),
                ),
            )
            ->setParameter('member', $member)
            ->setParameter('rMember', $member)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findActiveChangedUser(User $member): array
    {
        $surveyHistories = $this->getEntityManager()->createQueryBuilder()
            ->select('sh.entityId')
            ->from(History::class, 'sh')
            ->join(Survey::class, 'survey', 'WITH', (new Expr())->eq('sh.entityId', 'survey.id'))
            ->join(Log::class, 'slog', 'WITH', (new Expr())->andX((new Expr())->eq('sh.entityId', 'slog.entityId'), (new Expr())->eq('slog.entity', ':survey'), (new Expr())->eq('slog.user', ':member')))
            ->andWhere(
                (new Expr())->eq('sh.entity', ':survey'),
                (new Expr())->eq('sh.notify', ':notify'),
                (new Expr())->lt('slog.viewAt', 'sh.createdAt'),
            );

        $issueHistories = $this->getEntityManager()->createQueryBuilder()
            ->select('Isurvey.id')
            ->from(History::class, 'ih')
            ->join(SurveyIssue::class, 'surveyIssue', 'WITH', (new Expr())->eq('ih.entityId', 'surveyIssue.id'))
            ->join(Log::class, 'ilog', 'WITH', (new Expr())->andX((new Expr())->eq('ih.entityId', 'ilog.entityId'), (new Expr())->eq('ilog.entity', ':issue'), (new Expr())->eq('ilog.user', ':member')))
            ->join('surveyIssue.survey', 'Isurvey')
            ->andWhere(
                (new Expr())->eq('ih.entity', ':issue'),
                (new Expr())->eq('ih.notify', ':notify'),
                (new Expr())->lt('ilog.viewAt', 'ih.createdAt'),
            );

        return $this->createQueryBuilder('s')
            ->join('s.respondents', 'r')
            ->andWhere(
                (new Expr())->eq('s.disabled', ':disabled'),
                (new Expr())->lte('s.startAt', 'CURRENT_DATE()'),
                (new Expr())->gte('s.endAt', 'CURRENT_DATE()'),
                // (new Expr())->isNull('s.bikeRide'),
                (new Expr())->eq('r.user', ':member'),
                (new Expr())->orX(
                    (new Expr())->in('s.id', $surveyHistories->getDQL()),
                    (new Expr())->in('s.id', $issueHistories->getDQL())
                )
            )
            ->setParameters(new ArrayCollection([
                new Parameter('disabled', false),
                new Parameter('member', $member),
                new Parameter('survey', 'Survey'),
                new Parameter('issue', 'SurveyIssue'),
                new Parameter('notify', true),
            ]))
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneNotifiable(int $surveyId): ?Survey
    {
        $surveyIssues = $this->getEntityManager()->createQueryBuilder()
            ->select('issue.id')
            ->from(SurveyIssue::class, 'issue')
            ->join('issue.survey', 'issueSurvey')
            ->andWhere(
                (new Expr())->eq('issueSurvey.id', ':surveyId')
            )
            ;

        $histories = $this->getEntityManager()->createQueryBuilder()
            ->select((new Expr())->count('history.id'))
            ->from(History::class, 'history')
            ->orWhere(
                (new Expr())->andX(
                    (new Expr())->eq('history.entity', ':classNameSurvey'),
                    (new Expr())->eq('history.entityId', ':surveyId'),
                ),
                (new Expr())->andX(
                    (new Expr())->eq('history.entity', ':classNameSurveyIssue'),
                    (new Expr())->in('history.entityId', $surveyIssues->getDQL()),
                )
            );

        $responses = $this->getEntityManager()->createQueryBuilder()
            ->select((new Expr())->count('respondent.id'))
            ->from(Respondent::class, 'respondent')
            ->join('respondent.survey', 'respondentSurvey')
            ->andWhere(
                (new Expr())->eq('respondentSurvey.id', ':surveyId')
            )
            ;

        try {
            return $this->createQueryBuilder('s')
                    ->andWhere(
                        (new Expr())->eq('s.id', ':surveyId'),
                        (new Expr())->gt('(' . $histories->getDQL() . ')', ':noneResponse'),
                        (new Expr())->gt('(' . $responses->getDQL() . ')', ':noneResponse')
                    )
                    ->setParameters(new ArrayCollection([
                        new Parameter('classNameSurvey', 'survey'),
                        new Parameter('classNameSurveyIssue', 'surveyIssue'),
                        new Parameter('surveyId', $surveyId),
                        new Parameter('noneResponse', 0),
                    ]))
                    ->getQuery()
                    ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
