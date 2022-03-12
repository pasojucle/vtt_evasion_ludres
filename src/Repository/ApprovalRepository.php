<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Approval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Approval|null find($id, $lockMode = null, $lockVersion = null)
 * @method Approval|null findOneBy(array $criteria, array $orderBy = null)
 * @method Approval[]    findAll()
 * @method Approval[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprovalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Approval::class);
    }
}
