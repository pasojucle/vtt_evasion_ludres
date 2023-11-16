<?php

namespace App\Repository;

use App\Entity\LicenceSwornCertification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LicenceSwornCertification>
 *
 * @method LicenceSwornCertification|null find($id, $lockMode = null, $lockVersion = null)
 * @method LicenceSwornCertification|null findOneBy(array $criteria, array $orderBy = null)
 * @method LicenceSwornCertification[]    findAll()
 * @method LicenceSwornCertification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenceSwornCertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenceSwornCertification::class);
    }

//    /**
//     * @return LicenceSwornCertification[] Returns an array of LicenceSwornCertification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LicenceSwornCertification
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
