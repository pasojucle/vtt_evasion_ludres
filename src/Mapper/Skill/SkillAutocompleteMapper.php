<?php

declare(strict_types=1);

namespace App\Mapper\Skill;

use App\Entity\Skill;

class SkillAutocompleteMapper
{
    public function mapToChoices(array $skills, array $notAllowedIds): array
    {
        $results = [];
        /** @var Skill $skill */
        foreach ($skills as $skill) {
            $results[] = [
                'value' => $skill->getId(),
                'text' => $skill->getContent(),
                'disabled' => in_array($skill->getId(), $notAllowedIds, true),
            ];
        }

        return $results;
    }
}
