<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\Filter\MemberSkillFilter;
use App\Dto\State\MemberSkillDevelopmentData;
use App\Entity\Enum\LevelType;
use App\Entity\Member;
use App\Mapper\MemberSkill\MemberSkillReadMapper;
use App\Mapper\MemberSkill\MemberSkillUpdateMapper;
use App\Repository\MemberSkillRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\MemberParticipation\Enum\QueryScope;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractMemberSkillProvider
{
    use FilterHydratorTrait;

    public function __construct(
        protected MemberSkillReadMapper $memberSkillReadMapper,
        protected MemberSkillRepository $memberSkillRepository,
        protected SkillRepository $skillRepository,
        protected SkillCategoryRepository $skillCategoryRepository,
        protected MemberSkillUpdateMapper $memberSkillUpdateMapper,
        protected PaginatorService $paginator,
    ) {
    }

    protected function getQueryBuilder(MemberSkillFilter $filter, QueryScope $scope = QueryScope::LIST): QueryBuilder
    {
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
    protected function getMemberSkillDevelopmentData(Member $member): ?array
    {
        $level = $member->getLevel();
        return (LevelType::SCHOOL === $level?->getType())
            ? $this->skillCategoryRepository->getTotalSkillAcquiredByMemberAndCategory($member)
            : null;
    }
}
