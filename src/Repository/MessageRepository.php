<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Section;
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
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
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
}
