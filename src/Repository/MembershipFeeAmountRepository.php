<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MembershipFeeAmount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MembershipFeeAmount|null find($id, $lockMode = null, $lockVersion = null)
 * @method MembershipFeeAmount|null findOneBy(array $criteria, array $orderBy = null)
 * @method MembershipFeeAmount[]    findAll()
 * @method MembershipFeeAmount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MembershipFeeAmountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipFeeAmount::class);
    }

    public function findOneByLicence(int $coverage, bool $isNewMember, bool $hasFamilyMember): ?MembershipFeeAmount
    {
        try {
            return $this->createQueryBuilder('mfa')
                ->join('mfa.membershipFee', 'mf')
                ->andWhere(
                    (new Expr())->eq('mf.newMember', ':isNewMember'),
                    (new Expr())->eq('mfa.coverage', ':coverage'),
                    (new Expr())->eq('mf.additionalFamilyMember', ':hasFamilyMember'),
                )
                ->setParameter('isNewMember', $isNewMember)
                ->setParameter('coverage', $coverage)
                ->setParameter('hasFamilyMember', $hasFamilyMember)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NoResultException $e) {
            return null;
        }
    }
}
