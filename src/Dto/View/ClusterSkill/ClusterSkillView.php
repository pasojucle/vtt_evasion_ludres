<?php

declare(strict_types=1);

namespace App\Dto\View\ClusterSkill;

use App\Dto\View\MemberSkill\MemberSkillView;

readonly class ClusterSkillView
{
    /**
     * @param string $content
     * @param ClusterSkillMembersView[] $memberSkills
     */
    public function __construct(
        public string $content,
        public array $memberSkills
    ) {
    }
}
