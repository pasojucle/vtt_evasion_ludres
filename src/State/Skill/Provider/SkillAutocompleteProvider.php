<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Dto\Filter\SkillAutocompleteFilter;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Mapper\Skill\SkillAutocompleteMapper;
use App\Repository\SkillRepository;
use App\State\FilterHydratorTrait;
use Doctrine\ORM\QueryBuilder;

class SkillAutocompleteProvider
{
    use FilterHydratorTrait;
    public function __construct(
        private SkillRepository $skillRepository,
        private SkillAutocompleteMapper $skillAutocompleteMapper,
    ) {
    }

    public function getAutocompleteChoices(?string $query, SkillAutocompleteFilter $filter): array
    {
        /** @var SkillAutocompleteFilter $filter */
        $skills = $this->getQueryBuilder($query, $filter)->getQuery()->getResult();

        $notAllowedIds = match (true) {
            null !== $filter->member => $filter->member->getMemberSkills()->map(fn (MemberSkill $memberSkill) => $memberSkill->getSkill()->getId())->toArray(),
            null !== $filter->cluster => $filter->cluster->getSkills()->map(fn (Skill $skill) => $skill->getId())->toArray(),
            default => [],
        };
        
        return $this->skillAutocompleteMapper->mapToChoices($skills, $notAllowedIds);
    }

    private function getQueryBuilder(?string $query, SkillAutocompleteFilter $filter): QueryBuilder
    {
        $qb = $this->skillRepository->getSkillQuery();
        if ($query) {
            $this->skillRepository->filterContent($qb, $query);
        }

        if ($filter->category) {
            $this->skillRepository->filterCategory($qb, $filter->category);
        }

        if ($filter->level) {
            $this->skillRepository->filterLevel($qb, $filter->level);
        }

        return $qb;
    }
}
