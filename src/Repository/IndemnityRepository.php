<?php

namespace App\Repository;

use App\Entity\Indemnity;
use App\Repository\Interface\IndemnityRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Indemnity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Indemnity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Indemnity[]    findAll()
 * @method Indemnity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndemnityRepository extends ServiceEntityRepository implements IndemnityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Indemnity::class);
    }

    public function save(Indemnity $indemnity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($indemnity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Indemnity $indemnity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($indemnity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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
