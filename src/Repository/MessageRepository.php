<?php

namespace App\Repository;

use App\Entity\Enum\LevelType;
use App\Entity\Message;
use App\Entity\Section;
use App\Repository\Interface\MessageRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository implements MessageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(Message $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Message $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findMessageQuery(?Section $section): QueryBuilder
    {
        if (!$section) {
            return $this->createQueryBuilder('m');
        }
        return $this->createQueryBuilder('m')
            ->andWhere(
                (new Expr())->eq('m.section', ':section')
            )
            ->setParameter('section', $section)
        ;
    }

    public function findBySectionNameAndQuery(string $sectionId, ?string $query = null): array
    {
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('s.id', ':sectionId'));
        $parameters = [new Parameter('sectionId', $sectionId)];

        if ($query) {
            $andX->add((new Expr())->LIKE('m.id', ':query'));
            $parameters[] = new Parameter('query', sprintf('%%%s%%', $query));
        }
        return $this->createQueryBuilder('m')
            ->join('m.section', 's')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere(
                (new Expr())->in('m.id', ':ids')
            )
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByid(string $id): ?Message
    {
        try {
            return $this->createQueryBuilder('m')
                ->andWhere(
                    (new Expr())->eq('m.id', ':id')
                )
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function getMessageQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('me');
    }

    public function filterSection(QueryBuilder $qb, Section $section): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('me.section', ':section')
            )
            ->setParameter('section', $section);
    }

    public function filterLevelType(QueryBuilder $qb, LevelType $levelType): void
    {
        $qb
            ->andWhere(
                $qb->expr()->eq('me.levelType', ':levelType')
            )
            ->setParameter('levelType', $levelType);
    }

    public function filterLabel(QueryBuilder $qb, string $label): void
    {
        $qb
            ->andWhere(
                $qb->expr()->like('me.label', ':label')
            )
            ->setParameter('label', '%' . $label . '%');
    }

    public function filterSort(QueryBuilder $qb, string $sort): void
    {
        $direction = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('me.label', $direction);
    }
}
