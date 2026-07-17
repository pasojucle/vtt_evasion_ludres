<?php

declare(strict_types=1);

namespace App\State\Level\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\LevelFilter;
use App\Dto\View\ListView;
use App\Mapper\Level\LevelListMapper;
use App\Repository\LevelRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\Interface\ListProviderInterface;
use Doctrine\ORM\QueryBuilder;

class LevelListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private LevelRepository $levelRepository,
        private PaginatorService $paginator,
        private LevelListMapper $mapper,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
        /** @var LevelFilter $filter */
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

    private function getQueryBuilder(LevelFilter $filter): QueryBuilder
    {
        $qb = $this->levelRepository->getLevelQuery();

        $this->levelRepository->filterType($qb, $filter->type);

        if ($filter->sort) {
            $this->levelRepository->filterSortByTitle($qb, $filter->sort);
        } else {
            $this->levelRepository->filterSortByPosition($qb);
        }
    
        if (!$filter->showDeleted) {
            $this->levelRepository->filterActive($qb);
        }

        return $qb;
    }
}
