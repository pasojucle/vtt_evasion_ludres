<?php

declare(strict_types=1);

namespace App\Dto\View\MemberParticipation;

use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class MemberParticipationFilterView implements TurboStreamViewInterface
{
    public function __construct(
        public int $memberId,
        public array $queries,
        public string $period,
        public ?string $type,
        public LinkView $action,
    ) {
    }
}
