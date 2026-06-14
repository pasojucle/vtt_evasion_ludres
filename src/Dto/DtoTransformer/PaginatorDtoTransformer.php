<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\View\PaginatorView;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginatorDtoTransformer
{
    private ?string $currentRoute;

    private ?array $currentParams;

    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $router,
    ) {
    }

    public function fromEntities(Paginator $paginator, ?array $filters = [], ?string $targetRoute = null): PaginatorView
    {
        $PaginatorView = new PaginatorView();

        $PaginatorView->lastPage = (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());

        $PaginatorView->total = $paginator->count();

        $PaginatorView->currentPage = $this->getCurrentPage();

        $this->currentRoute = $targetRoute ?? $this->requestStack->getCurrentRequest()->attributes->get('_route');

        $this->currentParams = $this->requestStack->getCurrentRequest()->get('_route_params');

        if (!empty($filters)) {
            $this->currentParams = array_merge($this->currentParams, $filters);
        }
        $PaginatorView->first = $this->getPageData(1);
        $PaginatorView->last = $this->getPageData($PaginatorView->lastPage);
        $this->getPages($PaginatorView);
        $PaginatorView->previous = (1 < $PaginatorView->currentPage) ? $this->getPageData($PaginatorView->currentPage - 1) : null;
        $PaginatorView->next = ($PaginatorView->currentPage < $PaginatorView->lastPage) ? $this->getPageData($PaginatorView->currentPage + 1) : null;

        return $PaginatorView;
    }


    public function fromArray(array $data, int $itemsPerPage, int $currentPage, ?array $filters = [], ?string $targetRoute = null): PaginatorView
    {
        $PaginatorView = new PaginatorView();

        $total = count($data);

        $PaginatorView->lastPage = (int) ceil($total / $itemsPerPage);

        $PaginatorView->total = $total;

        $PaginatorView->currentPage = $currentPage;

        $this->currentRoute = $targetRoute ?? $this->requestStack->getCurrentRequest()->attributes->get('_route');

        $this->currentParams = $this->requestStack->getCurrentRequest()->get('_route_params');

        if (!empty($filters)) {
            $this->currentParams = array_merge($this->currentParams, $filters);
        }
        $PaginatorView->first = $this->getPageData(1);
        $PaginatorView->last = $this->getPageData($PaginatorView->lastPage);
        $this->getPages($PaginatorView);
        $PaginatorView->previous = (1 < $PaginatorView->currentPage) ? $this->getPageData($PaginatorView->currentPage - 1) : null;
        $PaginatorView->next = ($PaginatorView->currentPage < $PaginatorView->lastPage) ? $this->getPageData($PaginatorView->currentPage + 1) : null;
        
        return $PaginatorView;
    }

    private function getCurrentPage(): int
    {
        $querry = $this->requestStack->getCurrentRequest()->query->get('p');

        $currentPage = ($querry) ? (int) $querry : 1;

        return $currentPage;
    }

    private function getPages(PaginatorView &$PaginatorView): void
    {
        $start = 1;
        $end = $PaginatorView->lastPage;

        if (6 < $PaginatorView->lastPage) {
            $start = $PaginatorView->currentPage - 3;
            if ($start < 1) {
                $start = 1;
            }
            $end = $start + 5;
            if ($PaginatorView->lastPage < $end) {
                $end = $PaginatorView->lastPage;
                $start = $end - 5;
            }
        }
        if (1 === $start) {
            $PaginatorView->first = null;
        }
        if ($end === $PaginatorView->lastPage) {
            $PaginatorView->last = null;
        }
        $PaginatorView->pages = [];
        foreach (range($start, $end) as $page) {
            $PaginatorView->pages[] = $this->getPageData($page);
        }
    }

    private function getPageData(int $page): array
    {
        $currentParams = array_merge($this->currentParams, ['p' => $page]);
        return [
            'page' => $page,
            'url' => $this->router->generate($this->currentRoute, $currentParams),
        ];
    }
}
