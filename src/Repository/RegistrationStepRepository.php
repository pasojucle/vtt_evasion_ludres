<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RegistrationStep;
use App\Entity\RegistrationStepGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
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

    /**
     * @return RegistrationStep[] Returns an array of RegistrationStep objects
     */
    public function findByCategoryAndFinal(?int $category, bool $final, int $render): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.registrationStepGroup', 'rsg')
        ;
        $andX = $qb->expr()->andX();
        $orX = $qb->expr()->orx();
        $orX->add($qb->expr()->isNull('r.category'));
        if (null !== $category) {
            $orX->add($qb->expr()->eq('r.category', ':category'));
            $qb->setParameter('category', $category);
        }
        $andX->add($orX);
        $render = (RegistrationStep::RENDER_FILE === $render)
            ? [RegistrationStep::RENDER_FILE, RegistrationStep::RENDER_FILE_AND_VIEW]
            : [RegistrationStep::RENDER_VIEW, RegistrationStep::RENDER_FILE_AND_VIEW];
        if (!$final) {
            $andX->add($qb->expr()->in('r.testingRender', ':render'));
        } else {
            $andX->add($qb->expr()->in('r.finalRender', ':render'));
        }

        return $qb
            ->andWhere($andX)
            ->setParameter('render', $render)
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
                    (new Expr())->eq('LOWER(rg.title)', ':name')
                )
                ->setParameter('name', strtolower('Assurance'))
                ->getQuery()
                ->getOneOrNullResult()
                ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
