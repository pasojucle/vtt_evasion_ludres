<?php

namespace App\Repository;

use App\Entity\Enum\PracticeEnum;
use App\Entity\PublicRegistrationRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicRegistrationRate>
 */
class PublicRegistrationRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicRegistrationRate::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('prr')
            ->orderBy('prr.practice', 'ASC')
            ->addOrderBy('prr.FFVelo', 'ASC')
            ->addOrderBy('prr.maxAge', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByPracticeAndAgeAndFFVelo(PracticeEnum $practice, int $age, bool $isFFVelo): ?PublicRegistrationRate
    {
        try {
            return $this->createQueryBuilder('prr')
                ->andWhere(
                    (new Expr())->eq('prr.practice', ':practice'),
                    (new Expr())->gt('prr.maxAge', ':age'),
                    (new Expr())->orX(
                        (new Expr())->eq('prr.FFVelo', ':isFFVelo'),
                        (new Expr())->isNull('prr.FFVelo'),
                    ),
                )
                ->setParameter('practice', $practice)
                ->setParameter('age', $age)
                ->setParameter('isFFVelo', $isFFVelo)
                ->orderBy('prr.maxAge','ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
