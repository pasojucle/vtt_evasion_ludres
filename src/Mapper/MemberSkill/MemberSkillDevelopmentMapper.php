<?php

declare(strict_types=1);

namespace App\Mapper\MemberSkill;

use App\Dto\State\MemberSkillDevelopmentData;
use App\Dto\View\ProgressBarView;

class MemberSkillDevelopmentMapper
{
    /**
     * @return ProgressBarView[]
     */
    public function mapToView(array $memberSkillDevelopmentData): array
    {
        dump($memberSkillDevelopmentData);
        
        return array_map(function (array $totalSkillsByCategory) {
            $totalSkill = $totalSkillsByCategory['total'];
            $totalAcquired = (int) ($totalSkillsByCategory['totalAcquired'] ?? 0);
            return new ProgressBarView(
                title: $totalSkillsByCategory['name'],
                value: $totalAcquired,
                percentage: match (true) {
                    0 < $totalSkill => (int) round(($totalAcquired / $totalSkill * 100), 0),
                   $totalSkill < $totalAcquired => 100,
                   default => 0,
                },
            );
        }, $memberSkillDevelopmentData);
    }
}
