<?php

declare(strict_types=1);

namespace App\Dto\State;

final class MemberSkillDevelopmentData
{
    /**
     * @param array<int, array{id: int, name: string, total: int}> $totalSkillsByCategory
     * @param array<int, array{id: int, name: string, total: int}> $totalMemberSkillsByCategory
     */
    public function __construct(
        public array $totalSkillsByCategory,
        public array $totalMemberSkillsByCategory,
        public array $totalSkillAcquiredByMemberAndMember
    ) {
    }
}
