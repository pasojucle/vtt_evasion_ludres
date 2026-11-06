<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Core\Contract\Provider\AutocompleteProviderInterface;
use App\Core\Contract\Provider\ListFilteredProviderInterface;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ActivityAutocompleteFilter;
use App\Mapper\Activity\ActivityAutocompleteMapper;
use App\Repository\BikeRideRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements AutocompleteProviderInterface<ActivityAutocompleteFilter>
 */
class ActivityAutocompleteProvider implements AutocompleteProviderInterface, ListFilteredProviderInterface
{
    use FilterHydratorTrait;

    
    public function __construct(
        private BikeRideRepository $bikeRideRepository,
        private ActivityAutocompleteMapper $activityAutocompleteMapper,
    ) {
    }

    public function getAutocompleteChoices(?string $query, ?AbstractFilter $filter): array
    {
        $activities = $this->getQueryBuilder($query, $filter)->getQuery()->getResult();

        return $this->activityAutocompleteMapper->mapToChoices($activities);
    }

    private function getQueryBuilder(?string $query, ActivityAutocompleteFilter $filter): QueryBuilder
    {
        $qb = $this->bikeRideRepository->getActivityQuery();
        if ($query) {
            $this->bikeRideRepository->filterQuery($qb, $query);
        }

        if ($filter->startAt && $filter->endAt) {
            $this->bikeRideRepository->filterPeriod($qb, $filter->startAt, $filter->endAt);
        };

        return $qb;
    }
}
