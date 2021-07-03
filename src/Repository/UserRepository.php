<?php

namespace App\Repository;

use App\Entity\User;
use App\Service\LicenceService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

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
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

        /**
     * @return Event[] Returns an array of Event objects
     */

    public function findMemberQuery(array $filters): QueryBuilder
    {
        $currentSeason = $this->licenceService->getCurrentSeason();
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i')
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
            if (null !== $filters['category']) {
                $qb->innerJoin('u.licences', 'li')
                    ->andWhere(
                        $qb->expr()->eq('li.season', ':season'),
                        $qb->expr()->eq('li.category', ':category'),
                    )
                    ->setParameter('season', $currentSeason)
                    ->setParameter('category', $filters['category'])
                    ;
            }
            if (null !== $filters['level']) {
                $qb
                    ->andWhere(
                        $qb->expr()->eq('u.level', ':level'),
                    )
                    ->setParameter('level', $filters['level'])
                    ;
            }
        }
        return $qb
            ->andWhere(
                $qb->expr()->isNull('i.kinship')
            )
            ->orderBy('i.name', 'ASC')
        ;
    }


    public function findMaxId(): int
    {
        try {
            $result = $this->createQueryBuilder('u')
                ->select('u, MAX(u.id) as idMax')
                ->getQuery()
                ->getSingleResult()
            ;
            return (int) $result['idMax'];
        } catch (NoResultException $e) {
            return 0;
        }
    }
}
