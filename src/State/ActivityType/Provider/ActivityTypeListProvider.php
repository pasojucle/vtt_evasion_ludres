<?php

declare(strict_types=1);

namespace App\State\ActivityType\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\BikeRideTypeFilter;
use App\Dto\View\ListView;
use App\Mapper\ActivityType\ActivityTypeListMapper;
use App\Repository\BikeRideTypeRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\QueryBuilder;

class ActivityTypeListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private BikeRideTypeRepository $bikeRideTypeRepository,
        private PaginatorService $paginator,
        private ActivityTypeListMapper $mapper,
    ) {
    }

    /**
     * @param BikeRideTypeFilter $filter
     */
    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, ?HandlerContext $context = null): ListView
    {
        $qb = $this->getQueryBuilder($filter);

        $entities = $this->paginator->paginate(
            $qb,
            $context->page,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $context,
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
