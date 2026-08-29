<?php

declare(strict_types=1);

namespace App\Dto\View\MemberSkill;

use App\Dto\View\LinkView;

readonly class MemberSkillView
{
    public function __construct(
        public int $id,
        public string $content,
        public LinkView $unacquired,
        public LinkView $pending,
        public LinkView $acquired,
    ) {
    }
}
