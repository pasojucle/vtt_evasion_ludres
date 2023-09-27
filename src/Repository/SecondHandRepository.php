<?php

namespace App\Repository;

use App\Entity\SecondHand;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecondHand>
 *
 * @method SecondHand|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecondHand|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecondHand[]    findAll()
 * @method SecondHand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecondHandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecondHand::class);
    }

    public function save(SecondHand $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SecondHand $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSecondHandQuery(?bool $valid = null): QueryBuilder
    {
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('s.deleted', ':deleted'));
        $andX->add((new Expr())->eq('s.valid', ':valid'));
        
        $parameters = [
            'deleted' => false,
            'valid' => true,
        ];

        if (null !== $valid) {
            $parameters['valid'] = $valid;
        }
        return $this->createQueryBuilder('s')
           ->andWhere($andX)
           ->setParameters($parameters)
           ->orderBy('s.createdAt', 'DESC')
       ;
    }

    public function findSecondHandEnabled(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
           ->andWhere(
               (new Expr())->eq('s.deleted', ':deleted'),
               (new Expr())->eq('s.valid', ':valid'),
               (new Expr())->eq('s.disabled', ':disabled')
           )
           ->setParameters([
                'deleted' => false,
                'valid' => true,
                'disabled' => false,
            ])
           ->orderBy('s.createdAt', 'DESC')
       ;
    }

    public function findOutOfPeriod(DateTimeImmutable $deadline): array
    {
        return $this->createQueryBuilder('sh')
            ->andWhere(
                (new Expr())->lt('sh.createdAt', ':deadline'),
                (new Expr())->eq('sh.disabled', ':disabled'),
                (new Expr())->eq('sh.deleted', ':deleted'),
            )
            ->setParameters([
                'deadline' => $deadline,
                'disabled' => false,
                'deleted' => false,
            ])
            ->getQuery()
            ->getResult();
    }
}
