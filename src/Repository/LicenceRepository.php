<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method Licence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Licence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Licence[]    findAll()
 * @method Licence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private RequestStack $request)
    {
        parent::__construct($registry, Licence::class);
    }

    public function findOneByUserAndLastSeason(User $user): ?Licence
    {
        try {
            return $this->createQueryBuilder('li')
                ->andWhere(
                    (new Expr())->eq('li.user', ':user'),
                    (new Expr())->orX(
                        (new Expr())->eq('li.state', ':stateValided'),
                        (new Expr())->eq('li.state', ':stateFederation'),
                    ),
                    (new Expr())->eq('li.season', ':lastSeason'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('user', $user),
                    new Parameter('stateValided', LicenceStateEnum::YEARLY_FILE_RECEIVED),
                    new Parameter('stateFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED),
                    new Parameter('lastSeason', $this->request->getSession()->get('currentSeason') - 1)
                ]))
                ->getQuery()
                ->getOneOrNullResult()
                ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findByUserAndPeriod(User $user, int $totalSeasons): array
    {
        return $this->createQueryBuilder('li')
            ->andWhere(
                (new Expr())->eq('li.user', ':user'),
                (new Expr())->orX(
                    (new Expr())->eq('li.state', ':stateValided'),
                    (new Expr())->eq('li.state', ':stateFederation'),
                ),
                (new Expr())->gte('li.season', ':deadline'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('stateValided', LicenceStateEnum::YEARLY_FILE_RECEIVED),
                new Parameter('stateFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED),
                new Parameter('deadline', $this->request->getSession()->get('currentSeason') - ($totalSeasons + 1))
            ]))
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllByLastSeason(): array
    {
        $season = $this->request->getSession()->get('currentSeason');

        return $this->createQueryBuilder('li')
            ->andWhere(
                (new Expr())->gte('li.season', ':lastSeason')
            )
            ->setParameter('lastSeason', $season - 1)
            ->getQuery()
            ->getResult()
        ;
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

    public function findOneLicenceByNumerAndsSeason(string $query, int $season): ?Licence
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
