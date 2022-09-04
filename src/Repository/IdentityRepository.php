<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Identity;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Identity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Identity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Identity[]    findAll()
 * @method Identity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IdentityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Identity::class);
    }

    public function findByNameAndFirstName(string $name, string $firstName)
    {
        return $this->createQueryBuilder('i')
            ->andWhere(
                (new Expr())->like('LOWER(i.name)', ':name'),
                (new Expr())->like('LOWER(i.firstName)', ':firstName'),
                (new Expr())->isNull('i.kinship')
            )
            ->setParameter('name', strtolower($name))
            ->setParameter('firstName', strtolower($firstName))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMemberByUser(User $user): ?Identity
    {
        try {
            return $this->createQueryBuilder('i')
                ->andWhere(
                    (new Expr())->eq('i.user', ':user'),
                    (new Expr())->isNull('i.kinship')
                )
                ->setParameter('user', $user)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findKinShipsByUser(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere(
                (new Expr())->eq('i.user', ':user'),
                (new Expr())->isNotNull('i.kinship')
            )
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
    ;
    }

    public function findAllBirthplaceToConvert(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere(
                (new Expr())->isNotNull('i.birthplace'),
                (new Expr())->isNull('i.birthCommune'),
            )
            ->getQuery()
            ->getResult()
    ;
    }
}
