<?php

namespace App\Repository;

use App\Entity\Log;
use App\Entity\User;
use App\Entity\Survey;
use DateTimeImmutable;
use App\Entity\History;
use App\Entity\Respondent;
use App\Entity\SurveyIssue;
use Doctrine\ORM\Query\Expr;
use App\Service\SeasonService;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<History>
 *
 * @method History|null find($id, $lockMode = null, $lockVersion = null)
 * @method History|null findOneBy(array $criteria, array $orderBy = null)
 * @method History[]    findAll()
 * @method History[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SeasonService $seasonService
    ) {
        parent::__construct($registry, History::class);
    }

    public function save(History $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(History $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByRegistrationEntity(User $user, string $className, int $entityId, DateTimeImmutable $seasonStartAt): ?History
    {
        try {
            return $this->createQueryBuilder('h')
                ->andWhere(
                    (new Expr())->eq('h.entity', ':entity'),
                    (new Expr())->eq('h.entityId', ':entityId'),
                    (new Expr())->eq('h.user', ':user'),
                    (new Expr())->eq('h.createdAt', ':seasonStartAt'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('entity', $className),
                    new Parameter('entityId', $entityId),
                    new Parameter('user', $user),
                    new Parameter('seasonStartAt', $seasonStartAt),
                ]))
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findOneByEntity(string $className, int $entityId): ?History
    {
        try {
            return $this->createQueryBuilder('h')
                ->andWhere(
                    (new Expr())->eq('h.entity', ':entity'),
                    (new Expr())->eq('h.entityId', ':entityId'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('entity', $className),
                    new Parameter('entityId', $entityId),
                ]))
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findBySeason(User $user, int $season): array
    {
        $seasonPeriod = $this->seasonService->getSeasonPeriod($season);
        $qb = $this->createQueryBuilder('h')
            ->andWhere(
                (new Expr())->gte('h.createdAt', ':seasonStarAt'),
                (new Expr())->eq('h.user', ':user'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('seasonStarAt', $seasonPeriod['startAt']),
                new Parameter('user', $user)
            ]))
            ->getQuery()
            ->getResult();

        $histories = [];
        /** @var History $history */
        foreach ($qb as $history) {
            $histories[$history->getEntity()][$history->getEntityId()] = $history;
        }
        return $histories;
    }

    public function findBySurvey(Survey $survey, ?DateTimeImmutable $viewAt): array
    {
        $surveyIssues = $this->getEntityManager()->createQueryBuilder()
        ->select('issue.id')
        ->from(SurveyIssue::class, 'issue')
        ->andWhere(
            (new Expr())->eq('issue.survey', ':survey')
        )
        ;

        return $this->createQueryBuilder('h')
            ->andWhere(
                (new Expr())->orx(
                    (new Expr())->andX(
                        (new Expr())->eq('h.entity', ':classNameSurvey'),
                        (new Expr())->eq('h.entityId', ':entityId'),
                    ),
                    (new Expr())->andX(
                        (new Expr())->eq('h.entity', ':classNameSurveyIssue'),
                        (new Expr())->in('h.entityId', $surveyIssues->getDQL()),
                    )
                ),
                (new Expr())->gte('h.createdAt', ':viewAt')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('classNameSurvey', 'survey'),
                new Parameter('classNameSurveyIssue', 'surveyIssue'),
                new Parameter('survey', $survey),
                new Parameter('entityId', $survey->getId()),
                new Parameter('viewAt', $viewAt),
            ]))
            ->getQuery()
            ->getResult();
    }

    public function findNotifiableBySurvey(Survey $survey): array
    {
        $surveyIssues = $this->getEntityManager()->createQueryBuilder()
            ->select('issue.id')
            ->from(SurveyIssue::class, 'issue')
            ->andWhere(
                (new Expr())->eq('issue.survey', ':survey')
            )
            ;

        $hasResponses = $this->getEntityManager()->createQueryBuilder()
            ->select((new Expr())->count('respondent.id'))
            ->from(Respondent::class, 'respondent')
            ->andWhere(
                (new Expr())->eq('respondent.survey', ':survey')
            )
            ;

        return $this->createQueryBuilder('h')
            ->andWhere(
                (new Expr())->orx(
                    (new Expr())->andX(
                        (new Expr())->eq('h.entity', ':classNameSurvey'),
                        (new Expr())->eq('h.entityId', ':entityId'),
                    ),
                    (new Expr())->andX(
                        (new Expr())->eq('h.entity', ':classNameSurveyIssue'),
                        (new Expr())->in('h.entityId', $surveyIssues->getDQL()),
                    )
                ),
                (new Expr())->gt( '('.$hasResponses->getDQL().')', ':noneResponse')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('classNameSurvey', 'survey'),
                new Parameter('classNameSurveyIssue', 'surveyIssue'),
                new Parameter('survey', $survey),
                new Parameter('entityId', $survey->getId()),
                new Parameter('noneResponse', 0),
            ]))
            ->getQuery()
            ->getResult();
    }
}
