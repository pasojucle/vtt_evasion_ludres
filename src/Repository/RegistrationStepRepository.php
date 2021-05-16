<?php

namespace App\Repository;

use Doctrine\ORM\Query\Expr;
use App\Entity\RegistrationStep;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method RegistrationStep|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegistrationStep|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegistrationStep[]    findAll()
 * @method RegistrationStep[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistrationStep::class);
    }

    /**
    * @return RegistrationStep[] Returns an array of RegistrationStep objects
    */

    public function findByType($type): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.types', 't')
            ->andWhere(
                (new Expr())->eq('t.title', ':type')
            )
            ->setParameter('type', $type)
            ->orderBy('r.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /**
    * @return RegistrationStep[] Returns an array of RegistrationStep objects
    */

    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.type', 't')
            ->orderBy('r.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /**
    * @return RegistrationStep[] Returns an array of RegistrationStep objects
    */

    public function findOneByStep($step): ?RegistrationStep
    {
        try {
            return $this->createQueryBuilder('r')
            ->andWhere(
                (new Expr())->eq('r', ':step')
            )
            ->setParameter('step', $step)
            ->getQuery()
            ->getOneOrNullResult()
            ;

        } catch (QueryException $e) {
            return null;
        }
    }
}
