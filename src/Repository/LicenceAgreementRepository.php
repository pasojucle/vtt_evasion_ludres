<?php

namespace App\Repository;

use App\Entity\LicenceAgreement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LicenceAgreement>
 */
class LicenceAgreementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LicenceAgreement::class);
    }

    public function findOneByUserAndAggrementId(User $user, string $agreement): ?LicenceAgreement
    {
        try {
            return $this->createQueryBuilder('la')
                ->join('la.agreement', 'a')
                ->join('la.licence', 'l')
                ->andWhere(
                    (new Expr())->eq('a.id', ':agreement'),
                    (new Expr())->eq('l.user', ':user')
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('user', $user),
                    new Parameter('agreement', $agreement)
                ]))
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
