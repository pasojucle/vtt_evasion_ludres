<?php

declare(strict_types=1);

namespace App\State\Coverage\Trait;

use App\Dto\Filter\CoverageFilter;
use Doctrine\ORM\QueryBuilder;

trait CoverageDataProviderTrait
{
    private function getQueryBuilder(CoverageFilter $filter): QueryBuilder
    {
        $qb = $this->memberRepository->getMemberQuery();
        $this->memberRepository->filterSeason($qb, $this->seasonService->getCurrentSeason());
        $this->memberRepository->filterNotValidate($qb);

        if ($filter->member) {
            $this->memberRepository->filterMember($qb, $filter->member->getId());
        }

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->memberRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->sort) {
            $this->memberRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }

    public function filterNotValidate(QueryBuilder &$qb): void
    {
        $qb->andWhere(
            $qb->expr()->eq('li.currentSeasonForm', ':currentSeasonForm')
        )
            ->setParameter('currentSeasonForm', false)
        ;
    }
}
