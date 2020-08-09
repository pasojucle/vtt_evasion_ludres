<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
    * @return Article[] Returns an array of Article objects
    */

    public function findByTerm($term):array
    {
        $excludings = ['à', 'un', 'une', 'le', 'la', 'les', 'et', 'avec', 'de', 'du', 'si'];
        $columns = ['s.title', 'c.title', 'a.title', 'a.content'];
        $searchs = preg_split('#\s#', $term);

        $qb =  $this->createQueryBuilder('a')
            ->join('a.chapter', 'c')
            ->join('c.section', 's');

        $orX = $qb->expr()->orX();
        $termLiteral = $qb->expr()->literal('%'.$term.'%');

        $item = 0;
        foreach($columns as $column) {
            foreach($searchs as $search) {
                if (!in_array($search, $excludings)) {
                    $orX->add($qb->expr()->like($column, $qb->expr()->literal('%'.$search.'%')));
                    $item ++;
                }
            }
        }

        return $qb->orWhere(
                /*$qb->expr()->like('s.title', ),
                $qb->expr()->like('c.title', $qb->expr()->literal('%'.$term.'%')),
                $qb->expr()->like('a.title', $qb->expr()->literal('%'.$term.'%')),
                $qb->expr()->like('a.content', $qb->expr()->literal('%'.$term.'%'))*/
                $orX
            )
            ->orderBy('s.title', 'ASC')
            ->orderBy('c.title', 'ASC')
            ->orderBy('a.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
