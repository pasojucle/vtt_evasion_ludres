<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SecondHandCategoryFilter;
use App\Dto\View\ListView;
use App\Mapper\SecondHandCategory\SecondHandCategoryListMapper;
use App\Repository\SecondHandCategoryRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\Interface\ListProviderInterface;
use Doctrine\ORM\QueryBuilder;

class SecondHandCategoryListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private SecondHandCategoryRepository $secondHandCategoryRepository,
        private PaginatorService $paginator,
        private SecondHandCategoryListMapper $mapper,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
        /** @var SecondHandCategoryFilter $filter */
        $qb = $this->getQueryBuilder($filter);

        $entities = $this->paginator->paginate(
            $qb,
            $currentPage,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $route,
            $currentPage,
            $filter,
            $filterConfig
        );
    }

    private function getQueryBuilder(SecondHandCategoryFilter $filter): QueryBuilder
    {
        $qb = $this->secondHandCategoryRepository->getSecondHandCategoryQuery();

        if ($filter->name) {
            $this->secondHandCategoryRepository->filterName($qb, $filter->name);
        }

        if (!$filter->showDeleted) {
            $this->secondHandCategoryRepository->filterActive($qb);
        }

        if ($filter->sort) {
            $this->secondHandCategoryRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
