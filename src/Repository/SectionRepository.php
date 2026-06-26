<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @method Section|null find($id, $lockMode = null, $lockVersion = null)
 * @method Section|null findOneBy(array $criteria, array $orderBy = null)
 * @method Section[]    findAll()
 * @method Section[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private Security $security
    ) {
        parent::__construct($registry, Section::class);
    }

    /**
     * @return Section[] Returns an array of Section objects
     */
    public function findParameterGroups(): array
    {
        $qb = $this->createQueryBuilder('p');
        $roles = ['ROLE_ADMIN'];
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }

        return $qb
            ->andWhere(
                (new Expr())->in('p.role', ':roles'),
            )
            ->setParameter('roles', $roles)
            ->orderBy('p.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneById(string $id): ?Section
    {
        try {
            return $this->createQueryBuilder('s')
                ->andWhere(
                    (new Expr())->eq('s.id', ':id')
                )
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
