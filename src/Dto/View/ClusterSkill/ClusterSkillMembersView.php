<?php

declare(strict_types=1);

namespace App\Dto\View\ClusterSkill;

use App\Dto\View\LinkView;

readonly class ClusterSkillMembersView
{
    public function __construct(
        public int $id,
        public string $fullName,
        public LinkView $unacquired,
        public LinkView $pending,
        public LinkView $acquired,
    ) {
    }
}
