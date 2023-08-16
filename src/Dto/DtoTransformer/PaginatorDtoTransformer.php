<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\PaginatorDto;
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

    public function fromEntities(Paginator $paginator, ?array $filters = [], ?string $targetRoute = null): PaginatorDto
    {
        $paginatorDto = new PaginatorDto();

        $paginatorDto->lastPage = (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());

        $paginatorDto->total = $paginator->count();

        $paginatorDto->currentPage = $this->getCurrentPage();

        $this->currentRoute = $targetRoute ?? $this->requestStack->getCurrentRequest()->attributes->get('_route');

        $this->currentParams = $this->requestStack->getCurrentRequest()->get('_route_params');

        if (!empty($filters)) {
            $this->currentParams = array_merge($this->currentParams, $filters);
        }
        $paginatorDto->first = $this->getPageData(1);
        $paginatorDto->last = $this->getPageData($paginatorDto->lastPage);
        $this->getPages($paginatorDto);
        $paginatorDto->previous = (1 < $paginatorDto->currentPage) ? $this->getPageData($paginatorDto->currentPage - 1) : null;
        $paginatorDto->next = ($paginatorDto->currentPage < $paginatorDto->lastPage) ? $this->getPageData($paginatorDto->currentPage + 1) : null;

        return $paginatorDto;
    }

    private function getCurrentPage(): int
    {
        $querry = $this->requestStack->getCurrentRequest()->query->get('p');

        $currentPage = ($querry) ? (int) $querry : 1;

        return $currentPage;
    }

    private function getPages(PaginatorDto &$paginatorDto): void
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
            $paginatorDto->pages[] = $this->getPageData($page);
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
