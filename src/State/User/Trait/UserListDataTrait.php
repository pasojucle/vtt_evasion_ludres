<?php

declare(strict_types=1);

namespace App\State\User\Trait;

use App\Dto\Filter\UserFilter;
use Doctrine\ORM\QueryBuilder;

trait UserListDataTrait
{
    private function getQueryBuilder(UserFilter $filter): QueryBuilder
    {
        $qb = $this->memberRepository->getMemberQuery();

        if ($filter->member) {
            $this->memberRepository->filterMember($qb, $filter->member->getId());
        }

        if (is_int($filter->season)) {
            $this->memberRepository->filterSeason($qb, $filter->season);
        }

        if ($filter->isBoardMember) {
            $this->memberRepository->filterBoardMember($qb, $filter->isBoardMember);
        }

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->memberRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->permissions) {
            $this->memberRepository->filterPermission($qb, $filter->permissions);
        }

        if ($filter->sort) {
            $this->memberRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
