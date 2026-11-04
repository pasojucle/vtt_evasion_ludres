<?php

declare(strict_types=1);

namespace App\State\Participation\Trait;

use App\Dto\Filter\ParticipationFilter;
use Doctrine\ORM\QueryBuilder;

Trait ParticipationDataProviderTrait
{
    public function getQueryBuilder(QueryBuilder $qb, ParticipationFilter $filter): QueryBuilder
    {
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

        return $qb;
    }
}