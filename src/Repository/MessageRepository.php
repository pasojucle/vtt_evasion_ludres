<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\ParameterGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
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

    public function findMessageQuery(?ParameterGroup $section): QueryBuilder
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

    public function findBySectionNameAndQuery(string $sectionName, ?string $query = null): array
    {
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('pg.name', ':sectionName'));
        $parameters = ['sectionName' => $sectionName];

        if ($query) {
            $andX->add((new Expr())->LIKE('m.name', ':query'));
            $parameters['query'] = sprintf('%%%s%%', $query);
        }
        return $this->createQueryBuilder('m')
            ->join('m.section', 'pg')
            ->andWhere($andX)
            ->setParameters($parameters)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByNames(array $names): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere(
                (new Expr())->in('m.name', ':names')
            )
            ->setParameter('names', $names)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByName(string $name): ?Message
    {
        try {
            return $this->createQueryBuilder('m')
                ->andWhere(
                    (new Expr())->eq('m.name', ':name')
                )
                ->setParameter('name', $name)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
