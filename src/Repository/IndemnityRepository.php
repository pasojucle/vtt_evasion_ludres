<?php

namespace App\Repository;

use App\Entity\Indemnity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Indemnity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Indemnity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Indemnity[]    findAll()
 * @method Indemnity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndemnityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Indemnity::class);
    }

    /**
     * @return Indemnity[] Returns an array of Indemnity objects
     */

    public function findOrderByBikeRideType(): array
    {
        return $this->createQueryBuilder('i')
            // ->join('i.bikeRideType', 'brt')
            // ->orderBy('brt.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
