<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MembershipFee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MembershipFee|null find($id, $lockMode = null, $lockVersion = null)
 * @method MembershipFee|null findOneBy(array $criteria, array $orderBy = null)
 * @method MembershipFee[]    findAll()
 * @method MembershipFee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MembershipFeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipFee::class);
    }
}
