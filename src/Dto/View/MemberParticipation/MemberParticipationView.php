<?php

declare(strict_types=1);

namespace App\Dto\View\MemberParticipation;

use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class MemberParticipationView implements TurboStreamViewInterface
{
    /**
     * @param BadgeView[] $activities
     */
    public function __construct(
        public int $id,
        public array $activities,
        public LinkView $action,
    ) {
    }
}
