<?php

namespace App\Repository;

use App\Entity\History;
use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

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
    public function __construct(ManagerRegistry $registry)
    {
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

    public function findOneByRegistrationEntity(User $user, string $className, int $entityId, int $season): ?History
    {
        try {
            return $this->createQueryBuilder('h')
                ->andWhere(
                    (new Expr())->eq('h.entity', ':entity'),
                    (new Expr())->eq('h.entityId', ':entityId'),
                    (new Expr())->eq('h.user', ':user'),
                    (new Expr())->eq('h.season', ':season'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('entity', $className),
                    new Parameter('entityId', $entityId),
                    new Parameter('user', $user),
                    new Parameter('season', $season),
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
        $qb = $this->createQueryBuilder('h')
            ->andWhere(
                (new Expr())->eq('h.season', ':season'),
                (new Expr())->eq('h.user', ':user'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('season', $season),
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

    public function findBySurvey(Survey $survey): array
    {
        $surveyIssues = $this->getEntityManager()->createQueryBuilder()
        ->select('issue.id')
        ->from(SurveyIssue::class, 'issue')
        ->andWhere(
            (new Expr())->eq('issue.survey', ':survey')
        )
        ;

        return $this->createQueryBuilder('h')
            ->orWhere(
                (new Expr())->andX(
                    (new Expr())->eq('h.entity', ':classNameSurvey'),
                    (new Expr())->eq('h.entityId', ':entityId'),
                ),
                (new Expr())->andX(
                    (new Expr())->eq('h.entity', ':classNameSurveyIssue'),
                    (new Expr())->in('h.entityId', $surveyIssues->getDQL()),
                )
            )
            ->setParameters(new ArrayCollection([
                new Parameter('classNameSurvey', 'survey'),
                new Parameter('classNameSurveyIssue', 'surveyIssue'),
                new Parameter('survey', $survey),
                new Parameter('entityId', $survey->getId()),
            ]))
            ->getQuery()
            ->getResult();
    }
}
