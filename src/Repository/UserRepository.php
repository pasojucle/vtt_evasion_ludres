<?php

namespace App\Repository;

use DateTime;
use DateInterval;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Level;
use App\Entity\Licence;
use Doctrine\ORM\QueryBuilder;
use App\Service\LicenceService;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private LicenceService $licenceService;

    public function __construct(ManagerRegistry $registry, LicenceService $licenceService)
    {
        parent::__construct($registry, User::class);
        $this->licenceService = $licenceService;
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
        $currentSeason = $this->licenceService->getCurrentSeason();
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i')
            ->innerJoin('u.licences', 'li')
            ;

        if (!empty($filters)) {
            if (null !== $filters['fullName']) {
                $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->like('i.name', $qb->expr()->literal('%'.$filters['fullName'].'%')),
                            $qb->expr()->like('i.firstName', $qb->expr()->literal('%'.$filters['fullName'].'%')),
                        )
                    )
                    ;
            }
            if (null !== $filters['level']) {
                $type = null;
                if ($filters['level'] === Level::TYPE_ALL_MEMBER) {
                    $type = Level::TYPE_MEMBER;
                }
                if ($filters['level'] === Level::TYPE_ALL_FRAME) {
                    $type = Level::TYPE_FRAME;
                }
                if ($filters['level'] === Level::TYPE_ADULT) {
                    $qb
                        ->andWhere(
                            $qb->expr()->isNull('u.level'),
                        )
                    ;
                } elseif (null !== $type) {
                    $qb
                        ->join('u.level', 'l')
                        ->andWhere(
                            $qb->expr()->eq('l.type', ':type'),
                        )
                        ->setParameter('type', $type)
                        ;
                } else {
                    $qb
                        ->andWhere(
                            $qb->expr()->eq('u.level', ':level'),
                        )
                        ->setParameter('level', $filters['level'])
                        ;
                }

            }
            if (null !== $filters['status']) {
                if ($filters['status'] == Licence::STATUS_NONE) {
                    $maxSeason = $this->licenceService->getSeasonByStatus(Licence::STATUS_NONE);
                    $qb
                        ->groupBy('u.id')
                        ->having('MAX(li.season) < :maxSeason')
                        ->setParameter('maxSeason', $maxSeason)
                        ;
                } elseif ($filters['status'] === Licence::STATUS_WAITING_RENEW) {
                    $season = $this->licenceService->getSeasonByStatus(Licence::STATUS_WAITING_RENEW);
                    $qb 
                        ->groupBy('u.id')
                        ->having('MAX(li.season) = :season')
                        ->setParameter('season', $season);
                } elseif (in_array($filters['status'],[Licence::STATUS_TESTING_IN_PROGRESS, Licence::STATUS_TESTING_COMPLETE])) {
                    $having = 'COUNT(s.id) BETWEEN 1 and 2';
                    $andX = $qb->expr()->andX();
                    $andX->add($qb->expr()->eq('li.season', ':season'));
                    $andX->add($qb->expr()->eq('li.final', ':final'));
                    if ($filters['status'] === Licence::STATUS_TESTING_COMPLETE) {
                        $andX->add($qb->expr()->eq('s.isPresent', 1));
                        $having = 'COUNT(s.id) > 2';
                    }
                    $qb
                        ->join('u.sessions', 's')
                        ->andWhere($andX)
                        ->groupBy('u.id')
                        ->having($having)
                        ->setParameter('season', $currentSeason)
                        ->setParameter('final', 0)
                    ;
                } elseif ($filters['status'] === Licence::ALL_USERS) {
                    return $qb->andWhere(
                        $qb->expr()->isNull('i.kinship'),
                    )
                    ->orderBy('i.name', 'ASC');
                } elseif ($filters['status'] === Licence::STATUS_VALID) {
                    $qb
                        ->andWhere(
                            $qb->expr()->eq('li.season', ':season'),
                            $qb->expr()->eq('li.status', ':status'),
                            $qb->expr()->eq('li.final', ':final'),
                        )
                        ->setParameter('status', $filters['status'])
                        ->setParameter('season', $currentSeason)
                        ->setParameter('final', 1)
                    ;
                }
            }
        }
        return $qb
            ->andWhere(
                $qb->expr()->isNull('i.kinship'),
                $qb->expr()->gt('li.status', ':inProgress')
            )
            ->setParameter('inProgress', Licence::STATUS_WAITING_VALIDATE)
            ->orderBy('i.name', 'ASC')
        ;

        return $qb;
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
            return ++ $maxId;
        } catch (NoResultException $e) {
            return 0;
        }
    }

    public function findByFullName(?string $fullName, ?bool $hasCurrentSeason = false): array
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i');
        if (null !== $fullName) {
            $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(i.name)', $qb->expr()->literal('%'.strtolower($fullName).'%')),
                        $qb->expr()->like('LOWER(i.firstName)', $qb->expr()->literal('%'.strtolower($fullName).'%')),
                    )
                );
        }

        if (true === $hasCurrentSeason) {
            $currentSeason = $this->licenceService->getCurrentSeason();
            $qb->leftJoin('u.licences', 'l')
                ->andWhere(
                    $qb->expr()->eq('l.season', ':currentSeason')
                )
                ->setParameter('currentSeason', $currentSeason);
        }
        return $qb->andWhere(
                $qb->expr()->isNull('i.kinship')
            )
            ->orderBy('i.name')
            ->getQuery()
            ->getResult();
    }

    public function findUserLicenceInProgressQuery(?array $filters): QueryBuilder
    {
        $currentSeason = $this->licenceService->getCurrentSeason();

        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i')
            ->innerJoin('u.licences', 'li');
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

    public function findMinorAndTesting(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i')
            ->innerJoin('u.licences', 'l');

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
            ->getResult();
    }

}
