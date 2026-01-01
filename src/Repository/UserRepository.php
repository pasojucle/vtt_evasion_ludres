<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BoardRole;
use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Session;
use App\Entity\User;
use App\Service\SeasonService;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
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
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findMemberQuery(?array $filters): QueryBuilder
    {
        $qb = $this->createQuery();
        $isYearlyLicence = true;
        if (!empty($filters)) {
            if (array_key_exists('is_yearly_licence', $filters)) {
                $isYearlyLicence = (bool) $filters['is_yearly_licence'];
            }
            if (isset($filters['query'])) {
                $this->addCriteriaByName($qb, $filters['query']);
            }
            if (array_key_exists('user', $filters) && $filters['user'] instanceof User) {
                $this->addCriteriaByUser($qb, $filters['user']);
            }
            if (isset($filters['levels'])) {
                $this->addCriteriaByLevel($qb, $filters['levels']);
            }
            if (isset($filters['status']) && 1 === preg_match('#^SEASON_(\d{4})$#', $filters['status'], $matches)) {
                $this->addCriteriaBySeason($qb, (int) $matches[1]);
            }
            if (array_key_exists('season', $filters)) {
                $season = (1 === preg_match('#^(SEASON_(\d{4})|(\d+))$#', $filters['season'], $matches))
                    ? (int) sprintf('%s%s', $matches[2], array_key_exists(3, $matches) ? $matches[3] : '')
                    : $filters['season'];
                match ($season) {
                    $this->seasonService::MIN_SEASON_TO_TAKE_PART => $this->addCriteriaGteSeason($qb),
                    Licence::FILTER_IN_PROCESSING => $this->addCriteriaByLicenceStatus($qb, 'addCriteriaTestinInProgress', $isYearlyLicence),
                    Licence::FILTER_TESTING_IN_PROGRESS => $this->addCriteriaByLicenceStatus($qb, 'addCriteriaTestinInProgress', $isYearlyLicence),
                    default => $this->addCriteriaBySeason($qb, $season),
                };
            }
            if (isset($filters['permission']) && !empty($filters['permission'])) {
                $this->addCriteriaByPermission($qb, $filters['permission']);
            }
            if (isset($filters['bikeRide'])) {
                $this->addCriteriaWithNoSession($qb, (int) $filters['bikeRide']);
            }
        }

        if ($isYearlyLicence) {
            $this->addCriteriaMember($qb);
        }

        return $this->orderByASC($qb);
    }

    private function addCriteriaByPermission(QueryBuilder $qb, array $permissions): void
    {
        $qb
            ->leftJoin('u.userPermissions', 'up')
            ->andWhere(
                $qb->expr()->in('up.permission', ':permissions'),
            )
            ->setParameter('permissions', $permissions)
        ;
    }

    private function addCriteriaByLicenceStatus(QueryBuilder $qb, string $criteria, bool &$isFinalLicence): void
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $isFinalLicence = false;
        $this->$criteria($qb, $currentSeason);
    }

    private function addCriteriaWithNoSession(QueryBuilder $qb, int $bikeRideId): QueryBuilder
    {
        $usersWithSession = $this->getEntityManager()->createQueryBuilder()
            ->select('userwithsession.id')
            ->from(Session::class, 'session')
            ->join('session.cluster', 'cluster')
            ->join('session.user', 'userwithsession')
            ->join('cluster.bikeRide', 'bikeRide')
            ->andWhere(
                (new Expr())->eq('bikeRide.id', ':bikeRideId')
            );
        
        return $qb->andWhere(
            $qb->expr()->notIn('u.id', $usersWithSession->getDQL())
        )
            ->setParameter('bikeRideId', $bikeRideId);
    }

    public function findCoverageQuery(?array $filters): QueryBuilder
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $qb = $this->createQuery();
        if (isset($filters['query'])) {
            $this->addCriteriaByName($qb, $filters['query']);
        }
        if (!empty($filters['user'])) {
            $this->addCriteriaByUser($qb, $filters['user']);
        }
        if (isset($filters['levels'])) {
            $this->addCriteriaByLevel($qb, $filters['levels']);
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
                $qb->expr()->like('LOWER(i.name)', ':fullname'),
                $qb->expr()->like('LOWER(i.firstName)', ':fullname'),
            )
        )
        ->setParameter('fullname', sprintf('%%%s%%', strtolower($fullName)));
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
        $isBoardmember = false;
        if (!empty($filterLevels)) {
            foreach ($filterLevels as $level) {
                switch ($level) {
                    case Level::TYPE_ALL_MEMBER:
                        $types[] = Level::TYPE_SCHOOL_MEMBER;
                        break;
                    case Level::TYPE_ALL_FRAME:
                        $types[] = Level::TYPE_FRAME;
                        break;
                    case Level::TYPE_BOARD_MEMBER:
                        $isBoardmember = true;
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

        if ($isBoardmember) {
            $orX->add($qb->expr()->isNotNull('u.boardRole'));
        }

        if (0 < $orX->count()) {
            $qb->andWhere($orX);
        }
    }

    private function addCriteriaBySeason(QueryBuilder &$qb, int $season): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('li.season', ':season'),
                $qb->expr()->orX(
                    $qb->expr()->neq('li.state', ':trialPending'),
                    $qb->expr()->neq('li.state', ':yearlyPending'),
                )
            )
            ->setParameter('season', $season)
            ->setParameter('trialPending', LicenceStateEnum::TRIAL_FILE_PENDING)
            ->setParameter('yearlyPending', LicenceStateEnum::YEARLY_FILE_PENDING)
        ;
    }

    private function addCriteriaRegistrationBySeason(QueryBuilder &$qb, int $season): void
    {
        $qb
            ->orWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq('li.state', ':stateYearlySubmitted'),
                    $qb->expr()->eq('li.season', ':season')
                ),
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->eq('li.state', ':stateTrialSubmitted'),
                        $qb->expr()->eq('li.state', ':stateTrialValidated'),
                    ),
                    $qb->expr()->eq('li.season', ':season')
                ),
            )
            ->setParameter('season', $season)
            ->setParameter('stateTrialSubmitted', LicenceStateEnum::TRIAL_FILE_SUBMITTED)
            ->setParameter('stateTrialValidated', LicenceStateEnum::TRIAL_FILE_RECEIVED)
            ->setParameter('stateYearlySubmitted', LicenceStateEnum::YEARLY_FILE_SUBMITTED)
        ;
    }

    private function addCriteriaGteSeason(QueryBuilder &$qb): void
    {
        $qb
            ->groupBy('u.id')
            ->andHaving(
                $qb->expr()->gte($qb->expr()->max('li.season'), ':minSeasonToTakePart')
            )
            ->setParameter('minSeasonToTakePart', $this->seasonService->getMinSeasonToTakePart())
        ;
    }

    private function addCriteriaMember(QueryBuilder &$qb): void
    {
        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('li.state', ':yearlyValid'),
                    $qb->expr()->eq('li.state', ':yearlySentToFederation'),
                    $qb->expr()->eq('li.state', ':expired'),
                )
            )
            ->setParameter('yearlyValid', LicenceStateEnum::YEARLY_FILE_RECEIVED)
            ->setParameter('yearlySentToFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED)
            ->setParameter('expired', LicenceStateEnum::EXPIRED)
        ;
    }

    private function addCriteriaNew(QueryBuilder &$qb, int $season): void
    {
        $usersWhithOnlyOneLicence = $this->getEntityManager()->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join('user.licences', 'userLicence')
            ->groupBy('user.id')
            ->andHaving(
                $qb->expr()->eq($qb->expr()->count('userLicence.id'), 1),
                $qb->expr()->eq($qb->expr()->max('userLicence.season'), ':season'),
            );

        $qb
            ->andWhere(
                $qb->expr()->eq('li.state', ':statusNew'),
                $qb->expr()->in('u', $usersWhithOnlyOneLicence->getDQL()),
            )
            ->setParameter('statusNew', LicenceStateEnum::YEARLY_FILE_SUBMITTED)
            ->setParameter('season', $season)
            ->orderBy('i.name', 'ASC')
        ;
    }

    private function addCriteriaRenew(QueryBuilder &$qb, int $season): void
    {
        $usersWhithMoreThanLicence = $this->getEntityManager()->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join('user.licences', 'userLicence')
            ->groupBy('user.id')
            ->andHaving(
                $qb->expr()->gt($qb->expr()->count('userLicence.id'), 1),
            );

        $qb
            ->andWhere(
                $qb->expr()->eq('li.state', ':statusRenew'),
                $qb->expr()->in('u', $usersWhithMoreThanLicence->getDQL()),
                $qb->expr()->eq('li.season', ':season'),
            )
            ->setParameter('statusRenew', LicenceStateEnum::YEARLY_FILE_SUBMITTED)
            ->setParameter('season', $season)
            ->orderBy('i.name', 'ASC')
        ;
    }

    private function addCriteriaWaitingRenew(QueryBuilder &$qb, int $currentSeason): void
    {
        $usersWhithCurrentSeasonLicence = $this->getEntityManager()->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join('user.licences', 'userLicence')
            ->andWhere(
                $qb->expr()->eq('userLicence.season', ':currentSeason'),
                $qb->expr()->orX(
                    $qb->expr()->eq('userLicence.state', ':stateSubmitted'),
                    $qb->expr()->eq('userLicence.state', ':stateValidated'),
                    $qb->expr()->eq('userLicence.state', ':stateSentToFederation'),
                )
            );

        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('li.state', ':stateValidated'),
                    $qb->expr()->eq('li.state', ':stateSentToFederation'),
                    $qb->expr()->eq('li.state', ':stateExpired'),
                ),
                $qb->expr()->eq('li.season', ':previousSeason'),
                $qb->expr()->notIn('u', $usersWhithCurrentSeasonLicence->getDQL()),
            )
            ->setParameter('previousSeason', $currentSeason - 1)
            ->setParameter('currentSeason', $currentSeason)
            ->setParameter('stateSubmitted', LicenceStateEnum::YEARLY_FILE_SUBMITTED)
            ->setParameter('stateValidated', LicenceStateEnum::YEARLY_FILE_RECEIVED)
            ->setParameter('stateSentToFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED)
            ->setParameter('stateExpired', LicenceStateEnum::EXPIRED)
            ->orderBy('i.name', 'ASC')
        ;
    }

    private function addCriteriaTestinInProgress(QueryBuilder &$qb, int $season): void
    {
        $usersWithSessionsPresent = $this->getEntityManager()->createQueryBuilder()
            ->select('sessionsinprogresspresent.id')
            ->from(Session::class, 'sessionsinprogresspresent')
            ->join('sessionsinprogresspresent.user', 'userinprogresspresent')
            ->groupBy('userinprogresspresent.id')
            ->andWhere(
                $qb->expr()->eq('sessionsinprogresspresent.isPresent', 1)
            )
            ->andHaving(
                $qb->expr()->lt($qb->expr()->count('sessionsinprogresspresent.id'), 3)
            );
        $usersWithSessions = $this->getEntityManager()->createQueryBuilder()
            ->select('userinprogress.id')
            ->from(Session::class, 'sessionsinprogress')
            ->join('sessionsinprogress.user', 'userinprogress')
            ->groupBy('userinprogress.id')
            ->andHaving(
                $qb->expr()->lt($qb->expr()->count('sessionsinprogress.id'), 3)
            );
        $qb
            ->leftjoin('u.sessions', 's')
            ->andWhere(
                $qb->expr()->eq('li.season', ':season'),
                $qb->expr()->orX(
                    $qb->expr()->eq('li.state', ':trialSubmitted'),
                    $qb->expr()->eq('li.state', ':trialReceived'),
                ),
                $qb->expr()->orX(
                    $qb->expr()->isNull('s'),
                    $qb->expr()->in('u.id', $usersWithSessions->getDQL()),
                    $qb->expr()->in('u.id', $usersWithSessionsPresent->getDQL())
                )
            )
            ->setParameter('season', $season)
            ->setParameter('trialSubmitted', LicenceStateEnum::TRIAL_FILE_SUBMITTED)
            ->setParameter('trialReceived', LicenceStateEnum::TRIAL_FILE_RECEIVED)
        ;
    }

    private function addCriteriaToRegister(QueryBuilder &$qb, int $season): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('li.season', ':season'),
                $qb->expr()->eq('li.state', ':yearlyReceived'),
            )
            ->setParameter('season', $season)
            ->setParameter('yearlyReceived', LicenceStateEnum::YEARLY_FILE_RECEIVED)
        ;
    }

    private function addCriteriaInProcessing(QueryBuilder &$qb, int $season): void
    {
        $qb
            ->leftjoin('u.sessions', 's')
            ->andWhere(
                $qb->expr()->eq('li.season', ':season'),
                $qb->expr()->orx(
                    $qb->expr()->eq('li.state', ':stateTrialPending'),
                    $qb->expr()->eq('li.state', ':stateYearlyPending'),
                )
            )
            ->setParameter('season', $season)
            ->setParameter('stateTrialPending', LicenceStateEnum::TRIAL_FILE_PENDING)
            ->setParameter('stateYearlyPending', LicenceStateEnum::YEARLY_FILE_PENDING)
        ;
    }

    private function addCriteriaTestinComplete(QueryBuilder &$qb, int $season): void
    {
        // $usersWithSessions = $this->getEntityManager()->createQueryBuilder()
        //     ->select('userinprogress.id')
        //     ->from(Session::class, 'sessionsinprogress')
        //     ->join('sessionsinprogress.user', 'userinprogress')
        //     ->groupBy('userinprogress.id')
        //     ->andWhere(
        //         $qb->expr()->eq('sessionsinprogress.isPresent', 1)
        //     )
        //     ->andHaving(
        //         $qb->expr()->gt($qb->expr()->count('sessionsinprogress.id'), 2)
        //     );
        $qb
            ->leftjoin('u.sessions', 's')
            ->andWhere(
                $qb->expr()->eq('li.season', ':season'),
                $qb->expr()->eq('li.state', ':trialValidate'),
                // $qb->expr()->orx(
                //     $qb->expr()->eq('li.state', ':trialSubmitted'),
                //     $qb->expr()->eq('li.state', ':trialValidate'),
                // ),
                // $qb->expr()->in('u.id', $usersWithSessions->getDQL())
            )
            ->setParameter('season', $season)
            // ->setParameter('trialSubmitted', LicenceStateEnum::TRIAL_FILE_SUBMITTED)
            // ->setParameter('trialValidate', LicenceStateEnum::TRIAL_FILE_RECEIVED)
            ->setParameter('trialValidate', LicenceStateEnum::TRIAL_COMPLETED)
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

    // public function findUserLicenceInProgressQuery(?array $filters): QueryBuilder
    // {
    //     $currentSeason = $this->seasonService->getCurrentSeason();

    //     $qb = $this->createQueryBuilder('u')
    //         ->innerJoin('u.identities', 'i')
    //         ->innerJoin('u.licences', 'li')
    //     ;
    //     if (null !== $filters && array_key_exists('isFinal', $filters) && null !== $filters['isFinal']) {
    //         $qb->andWhere(
    //             $qb->expr()->eq('li.final', ':isFinal')
    //         )
    //             ->setParameter('isFinal', $filters['isFinal'])
    //         ;
    //     }

    //     return $qb
    //         ->andWhere(
    //             $qb->expr()->isNull('i.kinship'),
    //             $qb->expr()->orx(
    //                 $qb->expr()->eq('li.status', ':stateTrialPending'),
    //                 $qb->expr()->eq('li.status', ':stateYearlyPending'),
    //             ),
    //             $qb->expr()->eq('li.season', ':season'),
    //         )
    //         ->setParameter('stateTrialPending', LicenceStateEnum::TRIAL_FILE_PENDING)
    //         ->setParameter('stateYearlyPending', LicenceStateEnum::YEARLY_FILE_PENDING)
    //         ->setParameter('season', $currentSeason)
    //         ->orderBy('i.name', 'ASC')
    //         ;
    // }

    public function findlicenceInProgressQuery(?array $filters): QueryBuilder
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $qb = $this->createQuery();

        if (!empty($filters)) {
            if (isset($filters['fullName'])) {
                $this->addCriteriaByName($qb, $filters['fullName']);
            }
            if (isset($filters['user'])) {
                $this->addCriteriaByUser($qb, $filters['user']);
            }
            if (isset($filters['levels'])) {
                $this->addCriteriaByLevel($qb, $filters['levels']);
            }
            if (isset($filters['status'])) {
                match ((int) $filters['status']) {
                    Licence::FILTER_TESTING_IN_PROGRESS => $this->addCriteriaTestinInProgress($qb, $currentSeason),
                    Licence::FILTER_TESTING_COMPLETE => $this->addCriteriaTestinComplete($qb, $currentSeason),
                    Licence::FILTER_NEW => $this->addCriteriaNew($qb, $currentSeason),
                    Licence::FILTER_RENEW => $this->addCriteriaRenew($qb, $currentSeason),
                    Licence::FILTER_WAITING_RENEW => $this->addCriteriaWaitingRenew($qb, $currentSeason),
                    Licence::FILTER_IN_PROCESSING => $this->addCriteriaInProcessing($qb, $currentSeason),
                    Licence::FILTER_TO_REGISTER => $this->addCriteriaToRegister($qb, $currentSeason),
                    default => $this->addCriteriaRegistrationBySeason($qb, $currentSeason),
                };
            }
        }
        if (array_key_exists('status', $filters) && null === $filters['status']) {
            $this->addCriteriaRegistrationBySeason($qb, $currentSeason);
        }

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

    public function findFramers(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.sessions', 's')
            ->leftJoin('u.level', 'l')
            ->join('u.identities', 'i')
            ->join('u.licences', 'li');

        if (!empty($filters)) {
            if (array_key_exists('query', $filters) && null !== $filters['query']) {
                $this->addCriteriaByName($qb, $filters['query']);
            }
            if (!empty($filters['user'])) {
                $this->addCriteriaByUser($qb, $filters['user']);
            }
        }

        $this->addCriteriaGteSeason($qb);

        return $qb
            ->andWhere(
                (new Expr())->eq('l.type', ':levelType'),
                (new Expr())->eq('i.kind', ':member'),
            )
            ->setParameter('levelType', Level::TYPE_FRAME)
            ->setParameter('member', IdentityKindEnum::MEMBER)
            ->orderBy('i.name', 'ASC')
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
        ->setParameters(new ArrayCollection([
            new Parameter('query', '%' . strtolower($query) . '%'),
        ]))
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

    public function removeBoardRole(BoardRole $boardRole): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(User::class, 'u')
            ->set('u.boardRole', ':null')
            ->where(
                (new Expr())->eq('u.boardRole', ':boardRole')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('null', null),
                new Parameter('boardRole', $boardRole)
            ]))
            ->getQuery()
            ->execute();
    }

    public function findAllByCurrentSeason(): array
    {
        $season = $this->seasonService->getCurrentSeason();

        return $this->createQueryBuilder('u')
            ->leftJoin('u.licences', 'li')
            ->andWhere(
                (new Expr())->eq('li.season', ':season')
            )
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllMemberByCurrentSeason(): array
    {
        $season = $this->seasonService->getCurrentSeason();

        return $this->createQueryBuilder('u')
            ->join('u.licences', 'li')
            ->join('u.level', 'le')
            ->andWhere(
                (new Expr())->eq('li.season', ':season'),
                (new Expr())->neq('le.type', ':levelType'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('season', $season),
                new Parameter('levelType', Level::TYPE_FRAME)
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNewRegisteredBySeason(int $season): array|string
    {
        $currentSeasonUsers = $this->getEntityManager()->createQueryBuilder()
            ->select('lcsu.id')
            ->from(Licence::class, 'lcs')
            ->leftJoin('lcs.user', 'lcsu')
            ->andWhere(
                (new Expr())->eq('lcs.season', ':season'),
                (new Expr())->orX(
                    (new Expr())->eq('lcs.state', ':yearlySubmitted'),
                    (new Expr())->eq('lcs.state', ':yearlyValidated'),
                    (new Expr())->eq('lcs.state', ':yearlySentToFederation'),
                )
            );

        $lastSeasonUsers = $this->getEntityManager()->createQueryBuilder()
            ->select('llsu.id')
            ->from(Licence::class, 'lls')
            ->leftJoin('lls.user', 'llsu')
            ->andWhere(
                (new Expr())->eq('lls.season', ':lastSeason'),
            );

        return $this->createQueryBuilder('u')
            ->join('u.identities', 'i')
            ->andWhere(
                (new Expr())->eq('i.kind', ':kind'),
                (new Expr())->in('u.id', $currentSeasonUsers->getDQL()),
                (new Expr())->notIn('u.id', $lastSeasonUsers->getDQL()),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('kind', IdentityKindEnum::MEMBER),
                new Parameter('season', $season),
                new Parameter('lastSeason', $season - 1),
                new Parameter('yearlySubmitted', LicenceStateEnum::YEARLY_FILE_SUBMITTED),
                new Parameter('yearlyValidated', LicenceStateEnum::YEARLY_FILE_RECEIVED),
                new Parameter('yearlySentToFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED),
            ]))
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }

    public function findUnRegisteredBySeason(int $season): array|string
    {
        $currentSeasonUsers = $this->getEntityManager()->createQueryBuilder()
        ->select('lcsu.id')
        ->from(Licence::class, 'lcs')
        ->leftJoin('lcs.user', 'lcsu')
        ->andWhere(
            (new Expr())->eq('lcs.season', ':season'),
        );

        $lastSeasonUsers = $this->getEntityManager()->createQueryBuilder()
        ->select('llsu.id')
        ->from(Licence::class, 'lls')
        ->leftJoin('lls.user', 'llsu')
        ->andWhere(
            (new Expr())->eq('lls.season', ':lastSeason'),
            (new Expr())->orX(
                (new Expr())->eq('lls.state', ':yearlySubmitted'),
                (new Expr())->eq('lls.state', ':yearlyValidated'),
                (new Expr())->eq('lls.state', ':yearlySentToFederation'),
                (new Expr())->eq('lls.state', ':expired'),
            )
        );

        return $this->createQueryBuilder('u')
            ->join('u.identities', 'i')
            ->andWhere(
                (new Expr())->eq('i.kind', ':kind'),
                (new Expr())->notIn('u.id', $currentSeasonUsers->getDQL()),
                (new Expr())->in('u.id', $lastSeasonUsers->getDQL()),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('kind', IdentityKindEnum::MEMBER),
                new Parameter('season', $season),
                new Parameter('lastSeason', $season - 1),
                new Parameter('yearlySubmitted', LicenceStateEnum::YEARLY_FILE_SUBMITTED),
                new Parameter('yearlyValidated', LicenceStateEnum::YEARLY_FILE_RECEIVED),
                new Parameter('yearlySentToFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED),
                new Parameter('expired', LicenceStateEnum::EXPIRED),

            ]))
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }

    
    public function findReRegisteredBySeason(int $season): array|string
    {
        $currentSeasonUsers = $this->getEntityManager()->createQueryBuilder()
        ->select('lcsu.id')
        ->from(Licence::class, 'lcs')
        ->leftJoin('lcs.user', 'lcsu')
        ->andWhere(
            (new Expr())->eq('lcs.season', ':season'),
        );

        $lastSeasonUsers = $this->getEntityManager()->createQueryBuilder()
        ->select('llsu.id')
        ->from(Licence::class, 'lls')
        ->leftJoin('lls.user', 'llsu')
        ->andWhere(
            (new Expr())->eq('lls.season', ':lastSeason'),
            (new Expr())->orX(
                (new Expr())->eq('lls.state', ':yearlySubmitted'),
                (new Expr())->eq('lls.state', ':yearlyValidated'),
                (new Expr())->eq('lls.state', ':yearlySentToFederation'),
            )
        );

        return $this->createQueryBuilder('u')
            ->join('u.identities', 'i')
            ->andWhere(
                (new Expr())->eq('i.kind', ':kind'),
                (new Expr())->in('u.id', $currentSeasonUsers->getDQL()),
                (new Expr())->in('u.id', $lastSeasonUsers->getDQL()),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('kind', IdentityKindEnum::MEMBER),
                new Parameter('season', $season),
                new Parameter('lastSeason', $season - 1),
                new Parameter('yearlySubmitted', LicenceStateEnum::YEARLY_FILE_SUBMITTED),
                new Parameter('yearlyValidated', LicenceStateEnum::YEARLY_FILE_RECEIVED),
                new Parameter('yearlySentToFederation', LicenceStateEnum::YEARLY_FILE_REGISTRED),
            ]))
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }

    public function findOneByLicenceNumber(string $licenceNumber): ?User
    {
        try {
            return $this->createQueryBuilder('u')
                ->andWhere(
                    (new Expr())->eq('u.licenceNumber', ':licenceNumber')
                )
                ->setParameter('licenceNumber', $licenceNumber)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
