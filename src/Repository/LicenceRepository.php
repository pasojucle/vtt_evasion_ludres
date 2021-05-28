<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Licence;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Licence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Licence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Licence[]    findAll()
 * @method Licence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Licence::class);
    }

    // /**
    //  * @return Licence[] Returns an array of Licence objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findOneBySeasonForUser(int $season, User $user): ?Licence
    {
        try {
            return $this->createQueryBuilder('l')
            ->andWhere(
                (new Expr())->eq('l.user', ':user'),
                (new Expr())->eq('l.season', ':season')
            )
            ->setParameter('user', $user)
            ->setParameter('season', $season)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function hasLicence(int $season): ?Licence
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('season', $season))
            ;
        $licence = $this->licences->matching($criteria)->first();
        return ($licence) ? $licence : null;
    }
}
