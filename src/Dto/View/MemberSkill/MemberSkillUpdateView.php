<?php

declare(strict_types=1);

namespace App\Dto\View\MemberSkill;

use App\Dto\View\Interface\TurboStreamViewInterface;

readonly class MemberSkillUpdateView implements TurboStreamViewInterface
{
    public function __construct(
        public int $memberId,
        public MemberSkillView $memberSkill,
        public array $memberSkillDevelopment,
    ) {
    }

    public function getStreamTemplate(): string
    {
        return 'member_skill/admin/update.lazy.html.twig';
    }
}
