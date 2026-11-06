<?php

declare(strict_types=1);

namespace App\State\Participation\Trait;

use App\Dto\Filter\ParticipationFilter;
use Doctrine\ORM\QueryBuilder;

trait ParticipationDataProviderTrait
{
    public function getQueryBuilder(ParticipationFilter $filter): QueryBuilder
    {
        $qb = $this->sessionRepository->getSessionQuery();

        $this->sessionRepository->filterParticipated($qb);

        if ($filter->startAt && $filter->endAt) {
            $this->sessionRepository->filterPeriod($qb, $filter->startAt, $filter->endAt);
        };

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->sessionRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->practice) {
            $this->sessionRepository->filterPractice($qb, $filter->practice);
        }

        return $qb;
    }
}
