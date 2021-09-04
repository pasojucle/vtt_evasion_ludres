<?php

namespace App\Repository;

use App\Entity\Identity;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;

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
}
