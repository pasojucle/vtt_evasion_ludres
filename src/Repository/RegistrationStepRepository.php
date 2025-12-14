<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\DisplayModeEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\RegistrationStep;
use App\Entity\RegistrationStepGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RegistrationStep|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegistrationStep|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegistrationStep[]    findAll()
 * @method RegistrationStep[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegistrationStep::class);
    }

    public function remove(RegistrationStep $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return RegistrationStep[] Returns an array of RegistrationStep objects
     */
    public function findByCategoryAndFinal(?LicenceCategoryEnum $category, bool $isYearly, DisplayModeEnum $displayMode): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.registrationStepGroup', 'rsg')
        ;
        $andX = $qb->expr()->andX();
        $orX = $qb->expr()->orx();
        $orX->add($qb->expr()->eq('r.category', ':schoolAdAdult'));
        $qb->setParameter('schoolAdAdult', LicenceCategoryEnum::SCHOOL_AND_ADULT);
        if (null !== $category) {
            $orX->add($qb->expr()->eq('r.category', ':category'));
            $qb->setParameter('category', $category);
        }
        $andX->add($orX);
        $displayModes = (DisplayModeEnum::FILE === $displayMode)
            ? [DisplayModeEnum::FILE, DisplayModeEnum::SCREN_AND_FILE, DisplayModeEnum::FILE_AND_LINK]
            : [DisplayModeEnum::SCREEN, DisplayModeEnum::SCREN_AND_FILE];
        if (!$isYearly) {
            $andX->add($qb->expr()->in('r.trialDisplayMode', ':displayMode'));
        } else {
            $andX->add($qb->expr()->in('r.yearlyDisplayMode', ':displayMode'));
        }

        return $qb
            ->andWhere($andX)
            ->setParameter('displayMode', $displayModes)
            ->OrderBy('rsg.orderBy', 'ASC')
            ->addOrderBy('r.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByGroup(RegistrationStepGroup $group): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere(
                (new Expr())->eq('r.registrationStepGroup', ':group')
            )
            ->setParameter('group', $group)
            ->orderBy('r.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findCoverageStep(): ?RegistrationStep
    {
        try {
            return $this->createQueryBuilder('r')
                ->join('r.registrationStepGroup', 'rg')
                ->andWhere(
                    (new Expr())->eq('LOWER(rg.title)', ':name'),
                    (new Expr())->eq('r.personal', ':personal'),
                    (new Expr())->eq('r.trialDisplayMode', ':displayMode'),
                    (new Expr())->eq('r.yearlyDisplayMode', ':displayMode')
                )
                ->setParameters(new ArrayCollection([
                    new Parameter('name', strtolower('Assurance')),
                    new Parameter('personal', false),
                    new Parameter('displayMode', DisplayModeEnum::FILE)
                ]))
                ->getQuery()
                ->getOneOrNullResult()
                ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findByGroupAndCategory(int $group, LicenceCategoryEnum $category): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere(
                (new Expr())->eq('r.registrationStepGroup', ':group'),
                (new Expr())->eq('r.category', ':category')
            )
            ->setParameters(new ArrayCollection([
                new Parameter('group', $group),
                new Parameter('category', $category->value),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNexOrderByGroup(RegistrationStepGroup $group): int
    {
        $nexOrder = 0;
        $maxOrder = $this->createQueryBuilder('rs')
            ->select('MAX(rs.orderBy)')
            ->andWhere(
                (new Expr())->eq('rs.registrationStepGroup', ':group')
            )
            ->setParameter('group', $group)
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
