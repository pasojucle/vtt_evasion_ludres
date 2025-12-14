<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Identity;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function findOneByNameAndFirstName(string $name, string $firstName): ?Identity
    {
        try {
            return $this->createQueryBuilder('i')
                ->andWhere(
                    (new Expr())->like('LOWER(i.name)', ':name'),
                    (new Expr())->like('LOWER(i.firstName)', ':firstName'),
                )
                ->setParameter('name', strtolower($name))
                ->setParameter('firstName', strtolower($firstName))
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findMembersByUsers(Paginator|array $users): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere(
                (new Expr())->in('i.user', ':users'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('users', $users),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneKinShipsByUser(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere(
                (new Expr())->eq('i.user', ':user'),
                (new Expr())->neq('i.kind', ':member')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('member', IdentityKindEnum::MEMBER),
            ]))
            ->getQuery()
            ->getResult()
    ;
    }

    public function findAllBirthplaceToConvert(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere(
                (new Expr())->isNotNull('i.birthPlace'),
                (new Expr())->isNull('i.birthCommune'),
            )
            ->getQuery()
            ->getResult()
    ;
    }
}
