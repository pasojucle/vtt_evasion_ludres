<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\HealthQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HealthQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method HealthQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method HealthQuestion[]    findAll()
 * @method HealthQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HealthQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HealthQuestion::class);
    }
}
