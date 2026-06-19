<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Entity\Member;
use App\Service\SeasonService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Licence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Licence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Licence[]    findAll()
 * @method Licence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private SeasonService $seasonService)
    {
        parent::__construct($registry, Licence::class);
    }

    public function findOneByUserAndLastSeason(Member $member): ?Licence
    {
        try {
            return $this->createQueryBuilder('li')
                ->andWhere(
                    (new Expr())->eq('li.user', ':member'),
                    (new Expr())->orX(
                        (new Expr())->eq('li.state', ':stateValided'),
                        (new Expr())->eq('li.state', ':stateFederation'),
                        (new Expr())->eq('li.state', ':stateExpired'),
                    ),
                    (new Expr())->eq('li.season', ':lastSeason'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('member', $member),
                    new Parameter('stateValided', LicenceStateEnum::YEARLY_FILE_RECEIVED),
                    new Parameter('stateFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED),
                    new Parameter('stateExpired', LicenceStateEnum::EXPIRED),
                    new Parameter('lastSeason', $this->seasonService->getPreviousSeason()),
                ]))
                ->getQuery()
                ->getOneOrNullResult()
                ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findByUserAndPeriod(Member $member, int $totalSeasons): array
    {
        return $this->createQueryBuilder('li')
            ->andWhere(
                (new Expr())->eq('li.user', ':member'),
                (new Expr())->orX(
                    (new Expr())->eq('li.state', ':stateValided'),
                    (new Expr())->eq('li.state', ':stateFederation'),
                ),
                (new Expr())->gte('li.season', ':deadline'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('member', $member),
                new Parameter('stateValided', LicenceStateEnum::YEARLY_FILE_RECEIVED),
                new Parameter('stateFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED),
                new Parameter('deadline', $this->seasonService->getCurrentSeason() - ($totalSeasons + 1))
            ]))
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllByLastSeason(int $currentSeason): array
    {
        return $this->createQueryBuilder('li')
            ->select('li AS licence')
            ->addSelect('CASE WHEN lip.id IS NOT NULL THEN 1 ELSE 0 END as hasPreviousLicence')
            ->leftJoin(Licence::class, 'lip', 'WITH', (new Expr())->andX(
                (new Expr())->eq('lip.user', 'li.user'),
                (new Expr())->eq('lip.season', ':previousSeason'),
            ))
            ->andWhere('li.season = :currentSeason')
            ->setParameter('currentSeason', $currentSeason)
            ->setParameter('previousSeason', $currentSeason - 1)
            ->getQuery()
            ->getResult();
    }

    public function findAllRegistredFromSeason(int $season): array
    {
        return $this->createQueryBuilder('li')
            ->andWhere(
                (new Expr())->eq('li.season', ':season'),
                (new Expr())->eq('li.state', ':yearlyFileRegistred'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('season', $season),
                new Parameter('yearlyFileRegistred', LicenceStateEnum::YEARLY_FILE_REGISTRED),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneLicenceByNumerAndSeason(string $query, int $season): ?Licence
    {
        try {
            return $this->createQueryBuilder('li')
                ->join('li.user', 'u')
                ->andWhere(
                    (new Expr())->eq('li.season', ':season'),
                    (new Expr())->like('LOWER(u.licenceNumber)', ':query'),
                    (new Expr())->orx(
                        (new Expr())->eq('li.state', ':yearlyFileSubmitted'),
                        (new Expr())->eq('li.state', ':yearlyFileReceive'),
                        (new Expr())->eq('li.state', ':yearlyFileRegistred'),
                    )
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('season', $season),
                    new Parameter('yearlyFileSubmitted', LicenceStateEnum::YEARLY_FILE_SUBMITTED),
                    new Parameter('yearlyFileReceive', LicenceStateEnum::YEARLY_FILE_RECEIVED),
                    new Parameter('yearlyFileRegistred', LicenceStateEnum::YEARLY_FILE_REGISTRED),
                    new Parameter('query', '%' . strtolower($query) . '%'),
                ]))
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
