<?php

namespace App\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class PaginatorService
{
    public const PAGINATOR_PER_PAGE = 15;

    /**
     * @param QueryBuilder|Query $query
     * @param Request $request
     * @param int $limit
     * @return Paginator
     */
    public function paginate($query, $request, int $limit): Paginator
    {
        $currentPage = $request->query->getInt('p') ?: 1;

        $query
            ->setFirstResult($limit * ($currentPage - 1))
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return new Paginator($query);
    }

    /**
     * @param Paginator $paginator
     * @return int
     */
    public function lastPage(Paginator $paginator): int
    {
        return ceil($paginator->count() / $paginator->getQuery()->getMaxResults());

    }

    /**
     * @param Paginator $paginator
     * @return int
     */
    public function total(Paginator $paginator): int
    {
        return $paginator->count();
    }

    /**
     * @param Paginator $paginator
     * @return bool
     */
    public function currentPageHasNoResult(Paginator $paginator): bool
    {
        return !$paginator->getIterator()->count();
    }
}