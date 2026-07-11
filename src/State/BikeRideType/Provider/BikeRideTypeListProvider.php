<?php

declare(strict_types=1);

namespace App\State\BikeRideType\Provider;


use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\BikeRideTypeFilter;
use App\Dto\View\ListView;
use App\Mapper\BikeRideType\BikeRideTypeListMapper;
use App\Repository\BikeRideTypeRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\Interface\ListProviderInterface;
use Doctrine\ORM\QueryBuilder;

class BikeRideTypeListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private BikeRideTypeRepository $bikeRideTypeRepository,
        private PaginatorService $paginator,
        private BikeRideTypeListMapper $mapper,
    ) {
    }

    /**
     * @param BikeRideTypeFilter $filter
     */
    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
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

    private function getQueryBuilder(BikeRideTypeFilter $filter): QueryBuilder
    {
        $qb = $this->bikeRideTypeRepository->getBikeRideTypeQuery();

        if ($filter->name) {
            $this->bikeRideTypeRepository->filterName($qb, $filter->name);
        }

        if (!$filter->showDeleted) {
            $this->bikeRideTypeRepository->filterActive($qb);
        }

        if ($filter->sort) {
            $this->bikeRideTypeRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
