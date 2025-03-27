<?php

declare(strict_types=1);

namespace App\Repository;

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
                    (new Expr())->eq('li.status', ':status'),
                    (new Expr())->eq('li.season', ':lastSeason'),
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('user', $user),
                    new Parameter('status', Licence::STATUS_VALID),
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
                (new Expr())->eq('li.status', ':status'),
                (new Expr())->eq('li.final', ':isFinal'),
                (new Expr())->gte('li.season', ':deadline'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('status', Licence::STATUS_VALID),
                new Parameter('isFinal', true),
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

    public function findSeasons(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.season')
            ->where(
                (new Expr())->eq('l.user', ':user')
            )
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
