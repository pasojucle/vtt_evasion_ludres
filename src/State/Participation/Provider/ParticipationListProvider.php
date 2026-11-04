<?php

declare(strict_types=1);

namespace App\State\Participation\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\FilterInitializerInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ParticipationFilter;
use App\Dto\View\ListView;
use App\Mapper\LevelFilterMapper;
use App\Mapper\Participation\ParticipationListMapper;
use App\Repository\BikeRideRepository;
use App\Service\PaginatorService;
use App\Service\SeasonService;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements ListProviderInterface<ParticipationFilter>
 */
class ParticipationListProvider implements ListProviderInterface, FilterInitializerInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private BikeRideRepository $activityRepository,
        private PaginatorService $paginator,
        private ParticipationListMapper $mapper,
        private LevelFilterMapper $levelFilterMapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, HandlerContext $context): ListView
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

    /**
     * @param ParticipationFilter $filter
     */
    public function initializeFilters(AbstractFilter $filter, ?HandlerContext $context): void
    {
        $period = $this->seasonService->getCurrentSeasonPeriod();
        $filter->setDefaultPeriod($period['startAt'], $period['endAt']);
    }

    private function getQueryBuilder(ParticipationFilter $filter): QueryBuilder
    {
        $qb = $this->activityRepository->getTotalSessionsByActivityQuery();

        $this->activityRepository->filterParticipated($qb);

        if ($filter->startAt && $filter->endAt) {
            $this->activityRepository->filterPeriod($qb, $filter->startAt, $filter->endAt);
        };

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->activityRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->practice) {
            $this->activityRepository->filterPractice($qb, $filter->practice);
        }

        $this->activityRepository->filterSort($qb, 'ASC');

        return $qb;
    }
}
