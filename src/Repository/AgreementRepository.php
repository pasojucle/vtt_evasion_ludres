<?php

namespace App\Repository;

use App\Entity\Agreement;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agreement>
 */
class AgreementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agreement::class);
    }

    public function findSchoolAgreements(LicenceMembershipEnum $membership, array $existingLicenceConsents): array
    {
        $andX = (new Expr())->andX();
        $parameters = [];
        $this->addCategoryCriteria($andX, $parameters, LicenceCategoryEnum::SCHOOL);
        $this->addMembershipCriteria($andX, $parameters, $membership);
        $this->addExistingConsentsCriteria($andX, $parameters, $existingLicenceConsents);
        $this->addEnabledCriteria($andX, $parameters);

        return $this->createQueryBuilder('a')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findAdultAgreements(LicenceMembershipEnum $membership, array $existingLicenceConsents): array
    {
        $andX = (new Expr())->andX();
        $parameters = [];
        $this->addCategoryCriteria($andX, $parameters, LicenceCategoryEnum::ADULT);
        $this->addMembershipCriteria($andX, $parameters, $membership);
        $this->addExistingConsentsCriteria($andX, $parameters, $existingLicenceConsents);
        $this->addEnabledCriteria($andX, $parameters);

        return $this->createQueryBuilder('a')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
        ;
    }

    private function addCategoryCriteria(Andx &$andX, array &$parameters, LicenceCategoryEnum $category): void
    {
        $orX = (new Expr())->orX();
        $orX->add((new Expr())->eq('a.category', ':category'));
        $orX->add((new Expr())->eq('a.category', ':schoolAndAdult'));
        $parameters[] = new Parameter('category', $category);
        $parameters[] = new Parameter('schoolAndAdult', LicenceCategoryEnum::SCHOOL_AND_ADULT);
        $andX->add($orX);
    }

    private function addMembershipCriteria(Andx &$andX, array &$parameters, LicenceMembershipEnum $membership): void
    {
        $orX = (new Expr())->orX();
        $orX->add((new Expr())->eq('a.membership', ':membership'));
        $orX->add((new Expr())->eq('a.membership', ':trialAndYearly'));
        $parameters[] = new Parameter('membership', $membership);
        $parameters[] = new Parameter('trialAndYearly', LicenceMembershipEnum::TRIAL_AND_YEARLY);
        $andX->add($orX);
    }

    private function addExistingConsentsCriteria(Andx &$andX, array &$parameters, array $existingConsents): void
    {
        if (!empty($existingConsents)) {
            $andX->add((new Expr())->notIn('a.id', ':existingConsents'));
            $parameters[] = new Parameter('existingConsents', $existingConsents);
        }
    }

    private function addEnabledCriteria(Andx &$andX, array &$parameters): void
    {
        $andX->add((new Expr())->eq('a.enable', ':enable'));
        $parameters[] = new Parameter('enable', true);
    }

    public function findNexOrder(): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('a')
            ->select('MAX(a.orderBy)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (null !== $maxOrder) {
            $maxOrder = (int) $maxOrder;
            $nexOrder = $maxOrder + 1;
        }

        return $nexOrder;
    }
}
