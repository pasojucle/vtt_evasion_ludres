<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\Filter\AbstractFilter;
use App\Dto\PaginatorDto;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginatorMapper
{

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function fromEntities(Paginator $paginator, string $route, ?int $currentPage, AbstractFilter $filter): PaginatorDto
    {
        $paginatorDto = new PaginatorDto();

        $paginatorDto->lastPage = (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());

        $paginatorDto->total = $paginator->count();

        $paginatorDto->currentPage = $currentPage ?? 1;
        $queries = $filter->toArray();

        $paginatorDto->first = $this->getPageData(1, $route, $queries);
        $paginatorDto->last = $this->getPageData($paginatorDto->lastPage, $route, $queries);
        $this->getPages($paginatorDto, $route, $queries);
        $paginatorDto->previous = (1 < $paginatorDto->currentPage) ? $this->getPageData($paginatorDto->currentPage - 1, $route, $queries) : null;
        $paginatorDto->next = ($paginatorDto->currentPage < $paginatorDto->lastPage) ? $this->getPageData($paginatorDto->currentPage + 1, $route, $queries) : null;

        return $paginatorDto;
    }

    private function getPages(PaginatorDto &$paginatorDto, string $route, array $queries): void
    {
        $start = 1;
        $end = $paginatorDto->lastPage;

        if (6 < $paginatorDto->lastPage) {
            $start = $paginatorDto->currentPage - 3;
            if ($start < 1) {
                $start = 1;
            }
            $end = $start + 5;
            if ($paginatorDto->lastPage < $end) {
                $end = $paginatorDto->lastPage;
                $start = $end - 5;
            }
        }
        if (1 === $start) {
            $paginatorDto->first = null;
        }
        if ($end === $paginatorDto->lastPage) {
            $paginatorDto->last = null;
        }
        $paginatorDto->pages = [];
        foreach (range($start, $end) as $page) {
            $paginatorDto->pages[] = $this->getPageData($page, $route, $queries);
        }
    }

    private function getPageData(int $page, string $route, array $queries): array
    {
        $queries['page'] = $page;
        
        return [
            'page' => $page,
            'url' => $this->urlGenerator->generate($route, $queries),
        ];
    }
}
