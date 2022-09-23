<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\User;
use App\Service\SeasonService;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private SeasonService $seasonService;

    public function __construct(ManagerRegistry $registry, SeasonService $seasonService)
    {
        parent::__construct($registry, User::class);
        $this->seasonService = $seasonService;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findMemberQuery(?array $filters): QueryBuilder
    {
        $qb = $qb = $this->createQuery();

        if (!empty($filters)) {
            if (null !== $filters['fullName']) {
                $this->addCriteriaByName($qb, $filters['fullName']);
            }
            if (!empty($filters['user'])) {
                $this->addCriteriaByUser($qb, $filters['user']);
            }
            if (array_key_exists('levels', $filters) && null !== $filters['levels']) {
                $this->addCriteriaByLevel($qb, $filters['levels']);
            }
            if (array_key_exists('status', $filters) && null !== $filters['status'] && 1 === preg_match('#^SEASON_(\d{4})$#', $filters['status'], $matches)) {
                $this->addCriteriaBySeason($qb, (int) $matches[1]);
            }
        }

        $this->addCriteriaMember($qb);

        return $this->orderByASC($qb);
    }

    public function findCoverageQuery(?array $filters): QueryBuilder
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $qb = $this->createQuery();
        if (!empty($filters)) {
            if (null !== $filters['fullName']) {
                $this->addCriteriaByName($qb, $filters['fullName']);
            }
            if (null !== $filters['levels']) {
                $this->addCriteriaByLevel($qb, $filters['levels']);
            }
        }

        $this->addCriteriaBySeason($qb, $currentSeason);
        $this->addCriteriaMember($qb);

        $qb->andWhere(
            $qb->expr()->eq('li.currentSeasonForm', ':currentSeasonForm')
        )
            ->setParameter('currentSeasonForm', false)
        ;

        return $this->orderByASC($qb);
    }

    private function createQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->join('u.identities', 'i')
            ->join('u.licences', 'li')
            ->leftJoin('u.level', 'l')
        ;
    }

    private function orderByASC(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->andWhere(
                $qb->expr()->isNull('i.kinship'),
            )
            ->orderBy('i.name', 'ASC')
        ;
    }

    private function addCriteriaByName(QueryBuilder &$qb, string $fullName): void
    {
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->like('i.name', $qb->expr()->literal('%' . $fullName . '%')),
                $qb->expr()->like('i.firstName', $qb->expr()->literal('%' . $fullName . '%')),
            )
        )
        ;
    }

    private function addCriteriaByUser(QueryBuilder &$qb, User $user): void
    {
        $qb->andWhere(
            $qb->expr()->eq('u', ':user')
        )
        ->setParameter('user', $user)
        ;
    }

    private function addCriteriaByLevel(QueryBuilder &$qb, array $filterLevels): void
    {
        $types = [];
        $levels = [];
        if (!empty($filterLevels)) {
            foreach ($filterLevels as $level) {
                switch ($level) {
                    case Level::TYPE_ALL_MEMBER:
                        $types[] = Level::TYPE_SCHOOL_MEMBER;
                        break;
                    case Level::TYPE_ALL_FRAME:
                        $types[] = Level::TYPE_FRAME;
                        break;
                    case Level::TYPE_ADULT_MEMBER:
                        $types[] = Level::TYPE_ADULT_MEMBER;
                        break;
                    default:
                        $levels[] = $level;
                }
            }
        }
        $orX = $qb->expr()->orX();
        if (!empty($levels)) {
            $orX->add($qb->expr()->in('u.level', ':levels'));
            $qb
                ->setParameter('levels', $levels)
                ;
        }

        if (!empty($types)) {
            $orX->add($qb->expr()->in('l.type', ':types'));
            $qb->setParameter('types', $types);
        }

        if (0 < $orX->count()) {
            $qb->andWhere($orX);
        }
    }

    private function addCriteriaBySeason(QueryBuilder &$qb, int $season): void
    {
        $qb
            ->setParameter('season', $season)
            ->groupBy('u.id')
            ->andHaving(
                $qb->expr()->eq($qb->expr()->max('li.season'), ':season')
            )
        ;
    }

    private function addCriteriaMember(QueryBuilder &$qb): void
    {
        $qb
            ->andWhere(
                $qb->expr()->gt('li.status', ':status'),
                $qb->expr()->eq('li.final', ':final'),
            )
            ->setParameter('status', Licence::STATUS_WAITING_VALIDATE)
            ->setParameter('final', true)
        ;
    }

    private function addCriteriaNew(QueryBuilder &$qb): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('li.final', ':final'),
            )
            ->setParameter('final', true)
            ->groupBy('s.user')
            ->andHaving(
                $qb->expr()->eq($qb->expr()->count('li.id'), 1)
            )
            ->orderBy('i.name', 'ASC')
        ;
    }

    private function addCriteriaRenew(QueryBuilder &$qb): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('li.final', ':final'),
            )
            ->setParameter('final', true)
            ->groupBy('s.user')
            ->andHaving(
                $qb->expr()->gt($qb->expr()->count('li.id'), 1)
            )
            ->orderBy('i.name', 'ASC')
        ;
    }

    private function addCriteriaTestinInProgress(QueryBuilder &$qb): void
    {
        $qb
            ->leftjoin('u.sessions', 's')
            ->andWhere(
                $qb->expr()->eq('li.final', ':final'),
            )
            ->setParameter('final', false)
            ->groupBy('s.user')
            ->andHaving(
                $qb->expr()->lt($qb->expr()->count('s.id'), 3)
            )
        ;
    }

    private function addCriteriaTestinComplete(QueryBuilder &$qb): void
    {
        $qb
            ->join('u.sessions', 's')
            ->andWhere(
                $qb->expr()->eq('li.final', ':final'),
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->eq('s.isPresent', 1),
                        $qb->expr()->eq('l.type', ':type')
                    ),
                    $qb->expr()->neq('l.type', ':type'),
                    $qb->expr()->isnull('u.level')
                )
            )
            ->setParameter('final', false)
            ->setParameter('type', Level::TYPE_SCHOOL_MEMBER)
            ->groupBy('s.user')
            ->andHaving(
                $qb->expr()->gt($qb->expr()->count('s.id'), 2)
            )
            ->orderBy('i.name', 'ASC')
        ;
    }

    public function findNextId(): int
    {
        try {
            $result = $this->createQueryBuilder('u')
                ->select('u, MAX(u.id) as idMax')
                ->getQuery()
                ->getSingleResult()
            ;
            $maxId = (int) $result['idMax'];

            return ++$maxId;
        } catch (NoResultException $e) {
            return 0;
        }
    }

    public function findByFullName(?string $fullName, ?bool $hasCurrentSeason = false): array
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i')
        ;
        if (null !== $fullName) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(i.name)', $qb->expr()->literal('%' . strtolower($fullName) . '%')),
                    $qb->expr()->like('LOWER(i.firstName)', $qb->expr()->literal('%' . strtolower($fullName) . '%')),
                )
            );
        }

        if (true === $hasCurrentSeason) {
            $currentSeason = $this->seasonService->getCurrentSeason();
            $qb->leftJoin('u.licences', 'l')
                ->andWhere(
                    $qb->expr()->eq('l.season', ':currentSeason')
                )
                ->setParameter('currentSeason', $currentSeason)
            ;
        }

        return $qb->andWhere(
            $qb->expr()->isNull('i.kinship')
        )
            ->orderBy('i.name')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findUserLicenceInProgressQuery(?array $filters): QueryBuilder
    {
        $currentSeason = $this->seasonService->getCurrentSeason();

        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i')
            ->innerJoin('u.licences', 'li')
        ;
        if (null !== $filters && array_key_exists('isFinal', $filters) && null !== $filters['isFinal']) {
            $qb->andWhere(
                $qb->expr()->eq('li.final', ':isFinal')
            )
                ->setParameter('isFinal', $filters['isFinal'])
            ;
        }

        return $qb
            ->andWhere(
                $qb->expr()->isNull('i.kinship'),
                $qb->expr()->eq('li.status', ':inProgress'),
                $qb->expr()->eq('li.season', ':season'),
            )
            ->setParameter('inProgress', Licence::STATUS_WAITING_VALIDATE)
            ->setParameter('season', $currentSeason)
            ->orderBy('i.name', 'ASC')
            ;
    }

    public function findlicenceInProgressQuery(?array $filters): QueryBuilder
    {
        $currentSeason = $this->seasonService->getCurrentSeason();

        $qb = $this->createQuery();
dump($filters);
        if (!empty($filters)) {
            if (null !== $filters['fullName']) {
                $this->addCriteriaByName($qb, $filters['fullName']);
            }
            if (null !== $filters['user']) {
                $this->addCriteriaByUser($qb, $filters['user']);
            }
            if (null !== $filters['levels']) {
                $this->addCriteriaByLevel($qb, $filters['levels']);
            }
            if (null !== $filters['status']) {
                switch ($filters['status']) {
                    case Licence::STATUS_TESTING_IN_PROGRESS:
                        $this->addCriteriaTestinInProgress($qb);
                        break;
                    case Licence::STATUS_TESTING_COMPLETE:
                        $this->addCriteriaTestinComplete($qb);
                        break;
                    case Licence::STATUS_NEW:
                        $this->addCriteriaNew($qb);
                        break;
                    case Licence::STATUS_RENEW:
                        $this->addCriteriaRenew($qb);
                        break;
                }
            }
        }

        $this->addCriteriaBySeason($qb, $currentSeason);
        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('li.status', ':inProgress'),
                    $qb->expr()->eq('li.final', ':final')
                )
            )
            ->setParameter('inProgress', Licence::STATUS_WAITING_VALIDATE)
            ->setParameter('final', 0);

        return $this->orderByASC($qb);
    }

    public function findMinorAndTesting(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i')
            ->innerJoin('u.licences', 'l')
        ;

        $limit = new DateTime();
        $limit->sub(new DateInterval('P18Y'));

        return $qb->andWhere(
            $qb->expr()->isNull('i.kinship'),
            $qb->expr()->gte('i.birthDate', ':limit'),
            $qb->expr()->gte('l.season', ':season'),
        )
            ->setParameter('limit', $limit)
            ->setParameter('season', 2021)
            ->orderBy('i.name')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findFramers(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.sessions', 's')
            ->leftJoin('u.level', 'l')
            ->andWhere(
                (new Expr())->eq('l.type', ':levelType'),
            )
            ->setParameters([
                'levelType' => Level::TYPE_FRAME,
            ])
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByNumberLicenceOrFullName(string $query): array
    {
        return $this->createQueryBuilder('u')
        ->leftJoin('u.identities', 'i')
        ->orWhere(
            (new Expr())->like('LOWER(u.licenceNumber)', ':query'),
            (new Expr())->like('LOWER(i.name)', ':query'),
            (new Expr())->like('LOWER(i.firstName)', ':query'),
        )
        ->setParameters([
            'query' => '%' . strtolower($query) . '%',
        ])
        ->orderBy('u.licenceNumber', 'ASC')
        ->getQuery()
        ->getResult()
        ;
    }

    public function findAllAsc(): array
    {
        return $this->createQueryBuilder('u')
        ->leftJoin('u.identities', 'i')
        ->andWhere(
            (new Expr())->eq('u.protected', 0)
        )
        ->orderBy('u.licenceNumber', 'ASC')
        ->getQuery()
        ->getResult()
        ;
    }
}
