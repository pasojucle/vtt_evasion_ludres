<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Respondent;
use App\Entity\Survey;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
        $respondents = $this->_em->createQueryBuilder()
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
        return $this->createQueryBuilder('s')
            ->join('s.respondents', 'r')
            ->andWhere(
                (new Expr())->eq('s.disabled', 0),
                (new Expr())->lte('s.startAt', 'CURRENT_DATE()'),
                (new Expr())->gte('s.endAt', 'CURRENT_DATE()'),
                (new Expr())->isNull('s.bikeRide'),
                (new Expr())->eq('r.user', ':member'),
                (new Expr())->eq('r.surveyChanged', ':surveyChanged'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('member', $member),
                new Parameter('surveyChanged', true)
            ]))
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
