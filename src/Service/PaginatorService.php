<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class PaginatorService
{
    public const PAGINATOR_PER_PAGE = 15;

    /**
     * @param Query|QueryBuilder $query
     * @param Request            $request
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

    public function lastPage(Paginator $paginator): int
    {
        return (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());
    }

    public function total(Paginator $paginator): int
    {
        return $paginator->count();
    }

    public function currentPageHasNoResult(Paginator $paginator): bool
    {
        return !$paginator->getIterator()->count();
    }
}
