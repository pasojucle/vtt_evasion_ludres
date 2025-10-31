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

    public function paginate(Query|QueryBuilder $query, Request $request, int $limit): Paginator
    {
        $currentPage = $request->query->getInt('p') ?: 1;

        $query
            ->setFirstResult($limit * ($currentPage - 1))
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return new Paginator($query);
    }



    public function paginateFromArray(array $data, Request $request, int $limit): array
    {
        $currentPage = $request->query->getInt('p') ?: 1;

        $offset = $limit * ($currentPage - 1);
        $data = array_slice($data, $offset, $limit);

        return [$data, $currentPage];
    }
}
