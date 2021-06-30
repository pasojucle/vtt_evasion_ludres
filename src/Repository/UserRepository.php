<?php

namespace App\Repository;

use App\Entity\User;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
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

        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.identities', 'i')
            ;
        // if (null !== $filters['startAt'] && null !== $filters['endAt']) {
        //     $qb->andWhere(
        //         $qb->expr()->gte('e.startAt', ':startAt'),
        //         $qb->expr()->lte('e.startAt', ':endAt')
        //     )
        //     ->setParameter('startAt', $filters['startAt'])
        //     ->setParameter('endAt', $filters['endAt'])
        //     ;
        // }
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
