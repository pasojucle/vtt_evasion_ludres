<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    /**
     * @return User[] Returns an array of Userobjects
     */

    public function findMemberQuery(?array $filters = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l')
            ;

        if (!empty($filters)) {
            
        }
        return $qb
            ->orderBy('l.title', 'ASC')
        ;
    }
}
