<?php

declare(strict_types=1);

namespace App\State\BoardRole\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\BoardRoleFilter;
use App\Dto\View\ListView;
use App\Mapper\BoardRole\BoardRoleListMapper;
use App\Repository\BoardRoleRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\ListProviderInterface;
use Doctrine\ORM\QueryBuilder;

class BoardRoleListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private BoardRoleRepository $boardRoleRepository,
        private PaginatorService $paginator,
        private BoardRoleListMapper $mapper,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
        /** @var BoardRoleFilter $filter */
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

    private function getQueryBuilder(BoardRoleFilter $filter): QueryBuilder
    {
        $qb = $this->boardRoleRepository->getBoardRoleQuery();

        if ($filter->name) {
            $this->boardRoleRepository->filterName($qb, $filter->name);
        }

        if ($filter->sort) {
            $this->boardRoleRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
