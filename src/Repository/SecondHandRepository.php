<?php

namespace App\Repository;

use App\Entity\Log;
use App\Entity\SecondHand;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
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
        $criteria = (false === $valid) ? 'isNull' : 'isNotNull';
        $andX->add((new Expr())->$criteria('s.validedAt'));
        $parameters = [
            new Parameter('deleted', false),
        ];

        return $this->createQueryBuilder('s')
           ->andWhere($andX)
           ->setParameters(new ArrayCollection($parameters))
           ->orderBy('s.createdAt', 'DESC')
       ;
    }

    public function findSecondHandEnabledQuery(?DateTimeImmutable $viewAt = null): QueryBuilder
    {
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('s.deleted', ':deleted'));
        $andX->add((new Expr())->isNotNull('s.validedAt'));
        $andX->add((new Expr())->eq('s.disabled', ':disabled'));
        $parameters = [
            new Parameter('deleted', false),
            new Parameter('disabled', false),
        ];

        if ($viewAt) {
            $andX->add((new Expr())->gt('s.createdAt', ':viewAt'));
            $parameters[] = new Parameter('viewAt', $viewAt);
        }

        return $this->createQueryBuilder('s')
           ->andWhere($andX)
           ->setParameters(new ArrayCollection($parameters))
           ->orderBy('s.createdAt', 'DESC')
       ;
    }

    public function findSecondHandEnabled(?DateTimeImmutable $viewAt): array
    {
        return $this->findSecondHandEnabledQuery($viewAt)
            ->getQuery()
            ->getResult();
    }


    public function findOutOfPeriod(DateTimeImmutable $deadline): array
    {
        return $this->createQueryBuilder('sh')
            ->andWhere(
                (new Expr())->lt('sh.createdAt', ':deadline'),
                (new Expr())->eq('sh.disabled', ':disabled'),
                (new Expr())->eq('sh.deleted', ':deleted'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('deadline', $deadline),
                new Parameter('disabled', false),
                new Parameter('deleted', false),
            ]))
            ->getQuery()
            ->getResult();
    }

    public function findAllNotDeleted(): array
    {
        return $this->createQueryBuilder('s')
           ->andWhere(
               (new Expr())->eq('s.deleted', ':deleted')
           )
           ->setParameter('deleted', false)
           ->orderBy('s.createdAt', 'DESC')
           ->getQuery()
           ->getResult()
       ;
    }


    /**
     * @return SecondHand[] Returns an array of SecondHand objects
     */
    public function findNotViewedByUser(User $user): array
    {
        $viewed = $this->getEntityManager()->createQueryBuilder()
            ->select('log.entityId')
            ->from(Log::class, 'log')
            ->andWhere(
                (new Expr())->eq('log.user', ':user'),
                (new Expr())->eq('log.entity', ':entityName')
            );

        return $this->createQueryBuilder('s')
            ->andWhere(
                (new Expr())->notIn('s.id', $viewed->getDQL()),
                (new Expr())->isNotNull('s.validedAt'),
                (new Expr())->eq('s.disabled', ':disabled'),
            )
            ->setParameters(new ArrayCollection([
                new Parameter('user', $user),
                new Parameter('entityName', 'SecondHand'),
                new Parameter('disabled', false),
            ]))
            ->getQuery()
            ->getResult()
       ;
    }
}
