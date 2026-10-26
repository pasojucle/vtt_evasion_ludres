<?php

declare(strict_types=1);

namespace App\State\Skill\Trait;

use App\Dto\Filter\SkillFilter;
use App\Repository\MemberSkillRepository;
use App\Repository\SkillCategoryRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @property-read MemberSkillRepository $memberSkillRepository
 * @property-read SkillCategoryRepository $skillCategoryRepository
 */
trait SkillDataProviderTrait
{
    private function getQueryBuilder(SkillFilter $filter): QueryBuilder
    {
        $qb = $this->skillRepository->getSkillQuery();

        if ($filter->category) {
            $this->skillRepository->filterCategory($qb, $filter->category);
        }

        if ($filter->content) {
            $this->skillRepository->filterContent($qb, $filter->content);
        }

        if ($filter->level) {
            $this->skillRepository->filterLevel($qb, $filter->level);
        }

        if (!$filter->showDeleted) {
            $this->skillRepository->filterActive($qb);
        }
        
        if ($filter->sort) {
            $this->skillRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
