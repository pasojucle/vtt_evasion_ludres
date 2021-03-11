<?php

namespace App\Repository;

use App\Entity\Parameter;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Parameter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parameter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parameter[]    findAll()
 * @method Parameter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParameterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parameter::class);
    }

    /**
     * @return Parameters[] Returns an array of Parameters objects
    */

    public function findAll()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    public function findOneByName($name): ?Parameter
    {
        try {
            return $this->createQueryBuilder('p')
            ->andWhere('p.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        } catch (QueryException $e) {
            return null;
        }

    }
    
}
