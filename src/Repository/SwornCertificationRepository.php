<?php

namespace App\Repository;

use App\Entity\Licence;
use App\Entity\SwornCertification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SwornCertification>
 *
 * @method SwornCertification|null find($id, $lockMode = null, $lockVersion = null)
 * @method SwornCertification|null findOneBy(array $criteria, array $orderBy = null)
 * @method SwornCertification[]    findAll()
 * @method SwornCertification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SwornCertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SwornCertification::class);
    }

    public function deleteByLicence(Licence $licence): void
    {
        $this->createQueryBuilder('sc')
        ->delete()
        ->andWhere(
            (new Expr())->eq('sc.licence', ':licence')
        )
        ->setParameter('licence', $licence)
        ->getQuery()
        ->getResult()
    ;
    }
}
