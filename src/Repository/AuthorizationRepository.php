<?php

namespace App\Repository;

use App\Entity\Authorization;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Authorization>
 */
class AuthorizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Authorization::class);
    }

    /**
    * @return Authorization[] Returns an array of Authorization objects
    */
    public function findByCategory(LicenceCategoryEnum $category): array
    {
        return $this->createQueryBuilder('a')
            ->orWhere(
                (new Expr())->eq('a.category', ':category'),
                (new Expr())->eq('a.category', ':schoolAndAdult'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('schoolAndAdult', LicenceCategoryEnum::SCHOOL_AND_ADULT),
                new Parameter('category', $category),
            ]))
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }


    public function findSchoolAutorizations(LicenceMembershipEnum $membership, array $existingLicenceConsents): array
    {
        $andX = (new Expr())->andX();
        $parameters = [];
        $this->addCategoryCriteria($andX, $parameters, LicenceCategoryEnum::SCHOOL);
        $this->addMembershipCriteria($andX, $parameters, $membership);
        $this->addExistingAuthorizationsCriteria($andX, $parameters, $existingLicenceConsents);

        return $this->createQueryBuilder('c')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findAdultAuthorizations(LicenceMembershipEnum $membership, array $existingLicenceConsents): array
    {
        $andX = (new Expr())->andX();
        $parameters = [];
        $this->addCategoryCriteria($andX, $parameters, LicenceCategoryEnum::ADULT);
        $this->addMembershipCriteria($andX, $parameters, $membership);
        $this->addExistingAuthorizationsCriteria($andX, $parameters, $existingLicenceConsents);

        return $this->createQueryBuilder('c')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
        ;
    }

    private function addCategoryCriteria(Andx &$andX, array &$parameters, LicenceCategoryEnum $category): void
    {
        $orX = (new Expr())->orX();
        $orX->add((new Expr())->eq('c.category', ':category'));
        $orX->add((new Expr())->eq('c.category', ':schoolAndAdult'));
        $parameters[] = new Parameter('category', $category);
        $parameters[] = new Parameter('schoolAndAdult', LicenceCategoryEnum::SCHOOL_AND_ADULT);
        $andX->add($orX);
    }

    private function addMembershipCriteria(Andx &$andX, array &$parameters, LicenceMembershipEnum $membership): void
    {
        $orX = (new Expr())->orX();
        $orX->add((new Expr())->eq('c.membership', ':membership'));
        $orX->add((new Expr())->eq('c.membership', ':trialAndYearly'));
        $parameters[] = new Parameter('membership', $membership);
        $parameters[] = new Parameter('trialAndYearly', LicenceMembershipEnum::TRIAL_AND_YEARLY);
        $andX->add($orX);
    }

    private function addExistingAuthorizationsCriteria(Andx &$andX, array &$parameters, array $existingAuthorizations): void
    {
        if (!empty($existingAuthorizations)) {
            $andX->add((new Expr())->notIn('c.id', ':existingAuthorizations'));
            $parameters[] = new Parameter('existingAuthorizations', $existingAuthorizations);
        }
    }
}
