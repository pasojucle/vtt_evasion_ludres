<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Trait;

use App\Dto\Filter\MemberSkillFilter;
use App\Entity\Enum\LevelType;
use App\Entity\Member;
use App\State\MemberParticipation\Enum\QueryScope;
use Doctrine\ORM\QueryBuilder;

trait MemberSkillDataProviderTrait
{
    private function getQueryBuilder(
        MemberSkillFilter $filter,
        QueryScope $scope = QueryScope::LIST
    ): QueryBuilder {
        $qb = $this->memberSkillRepository->getMemberSkillQuery();

        $this->memberSkillRepository->filterUser($qb, $filter->member);

        if ($filter->evaluation) {
            $this->memberSkillRepository->filterEvaluation($qb, $filter->evaluation);
        }

        if ($filter->category) {
            $this->memberSkillRepository->filterCategory($qb, $filter->category);
        }

        if ($filter->level) {
            $this->memberSkillRepository->filterLevel($qb, $filter->level);
        }

        if ($filter->sort) {
            $this->memberSkillRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }

    /**
     * @return array<int, array{id: int, name: string, total: int, totalAcquired: int}>|null
     */
    private function getMemberSkillDevelopmentData(Member $member): ?array
    {
        $level = $member->getLevel();

        return (LevelType::SCHOOL === $level?->getType())
            ? $this->skillCategoryRepository->getTotalSkillAcquiredByMemberAndCategory($member)
            : null;
    }
}