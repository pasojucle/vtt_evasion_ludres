<?php

namespace App\Repository;

use Doctrine\ORM\Query\Expr;
use App\Entity\RegistrationStep;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function findByCategoryAndFinal(? int $category, bool $final, int $render): array
    {
        $qb = $this->createQueryBuilder('r');
        $orX = $qb->expr()->orx();
        $orX->add($qb->expr()->isNull('r.category'));

        if (null !== $category) {
            $orX->add($qb->expr()->eq('r.category', ':category'));
            $qb->setParameter('category', $category);
        }
        if (!$final) {
            $testingRender = (RegistrationStep::RENDER_FILE === $render)
                ? RegistrationStep::TESTING_RENDER_FILE
                : RegistrationStep::TESTING_RENDER_FILE_AND_VIEW;

            $qb->            
                andWhere(
                    $qb->expr()->gte('r.testingRender', ':testingRender')
                )
                ->setParameter('testingRender', $testingRender);
        }

        return $qb
            ->andWhere($orX)
            ->orderBy('r.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /**
    * @return RegistrationStep[] Returns an array of RegistrationStep objects
    */

    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.orderBy', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
