<?php

declare(strict_types=1);

namespace App\State\SecondHand\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\SecondHandFilter;
use App\Dto\View\ListView;
use App\Mapper\SecondHand\SecondHandListMapper;
use App\Repository\SecondHandRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\Interface\FilterInitializerInterface;
use App\State\Interface\ListProviderInterface;
use Doctrine\ORM\QueryBuilder;

class SecondHandListProvider implements ListProviderInterface, FilterInitializerInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private SecondHandRepository $secondHandRepository,
        private PaginatorService $paginator,
        private SecondHandListMapper $mapper,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
        /** @var SecondHandFilter $filter */
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

    public function initializeFilters(AbstractFilter $filter): void
    {
        /** @var SecondHandFilter $filter */
        $filter->setDefaultState();
    }

    private function getQueryBuilder(SecondHandFilter $filter): QueryBuilder
    {
        $qb = $this->secondHandRepository->getSecondHandQuery();

        if ($filter->state) {
            $this->secondHandRepository->filterState($qb, $filter->state);
        }

        if ($filter->category) {
            $this->secondHandRepository->filterCategory($qb, $filter->category);
        }

        if ($filter->member) {
            $this->secondHandRepository->filterMember($qb, $filter->member);
        }

        if ($filter->sort) {
            $this->secondHandRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
