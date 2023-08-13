<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\RegistrationChange;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;

/**
 * @extends ServiceEntityRepository<RegistrationChange>
 *
 * @method RegistrationChange|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegistrationChange|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegistrationChange[]    findAll()
 * @method RegistrationChange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistrationChange::class);
    }

    public function save(RegistrationChange $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RegistrationChange $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findOneByEntity(User $user, string $className, int $entityId, int $season): ?RegistrationChange
    {
        try {
            return $this->createQueryBuilder('r')
                ->andWhere(
                    (new Expr)->eq('r.entity', ':entity'),
                    (new Expr)->eq('r.entityId', ':entityId'),
                    (new Expr)->eq('r.user', ':user'),
                    (new Expr)->eq('r.season', ':season'),
                )
                ->setParameters([
                    'entity' => $className,
                    'entityId' => $entityId,
                    'user' => $user,
                    'season' => $season,
                ])
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }


    public function findOneBySeason(User $user, string $className, int $season): ?RegistrationChange
    {
        try {
            return $this->createQueryBuilder('r')
                ->andWhere(
                    (new Expr)->eq('r.entity', ':entity'),
                    (new Expr)->eq('r.season', ':season'),
                    (new Expr)->eq('r.user', ':user'),
                )
                ->setParameters([
                    'entity' => $className,
                    'season' => $season,
                    'user' => $user
                ])
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
