<?php

declare(strict_types=1);

namespace App\ViewModel\Paginator;

use App\ViewModel\AbstractViewModel;
use App\ViewModel\ServicesPresenter;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatorViewModel extends AbstractViewModel
{
    public ?int $currentPage;

    public ?int $lastPage;

    public ?int $total;

    public array $pages = [];

    public ?array $previous;

    public ?array $next;

    public ?array $first;

    public ?array $last;

    private ?string $currentRoute;

    private ?array $currentParams;

    private ServicesPresenter $services;

    public static function fromPaginator(Paginator $paginator, ?array $filters, ?string $targetRoute, ServicesPresenter $services)
    {
        $paginatorViewModel = new self();

        $paginatorViewModel->services = $services;

        $paginatorViewModel->lastPage = (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());

        $paginatorViewModel->total = $paginator->count();

        $paginatorViewModel->currentPage = $paginatorViewModel->getCurrentPage();

        $paginatorViewModel->currentRoute = $targetRoute ?? $services->requestStack->getCurrentRequest()->attributes->get('_route');

        $paginatorViewModel->currentParams = $services->requestStack->getCurrentRequest()->get('_route_params');

        if (!empty($filters)) {
            $paginatorViewModel->currentParams = array_merge($paginatorViewModel->currentParams, $filters);
        }
        $paginatorViewModel->first = $paginatorViewModel->getPageData(1);
        $paginatorViewModel->last = $paginatorViewModel->getPageData($paginatorViewModel->lastPage);
        $paginatorViewModel->pages = $paginatorViewModel->getPages();
        $paginatorViewModel->previous = (1 < $paginatorViewModel->currentPage) ? $paginatorViewModel->getPageData($paginatorViewModel->currentPage - 1) : null;
        $paginatorViewModel->next = ($paginatorViewModel->currentPage < $paginatorViewModel->lastPage) ? $paginatorViewModel->getPageData($paginatorViewModel->currentPage + 1) : null;

        return $paginatorViewModel;
    }

    private function getCurrentPage(): int
    {
        $querry = $this->services->requestStack->getCurrentRequest()->query->get('p');

        $currentPage = ($querry) ? (int) $querry : 1;

        return $currentPage;
    }

    private function getPages(): array
    {
        $start = 1;
        $end = $this->lastPage;

        if (6 < $this->lastPage) {
            $start = $this->currentPage - 3;
            if ($start < 1) {
                $start = 1;
            }
            $end = $start + 5;
            if ($this->lastPage < $end) {
                $end = $this->lastPage;
                $start = $end - 5;
            }
        }
        if (1 === $start) {
            $this->first = null;
        }
        if ($end === $this->lastPage) {
            $this->last = null;
        }
        $pages = [];
        foreach (range($start, $end) as $page) {
            $pages[] = $this->getPageData($page);
        }

        return $pages;
    }

    private function getPageData($page): array
    {
        $currentParams = array_merge($this->currentParams, ['p' => $page]);
        return [
            'page' => $page,
            'url' => $this->services->router->generate($this->currentRoute, $currentParams),
        ];
    }
}
