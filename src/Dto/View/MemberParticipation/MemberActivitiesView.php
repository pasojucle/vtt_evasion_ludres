<?php

declare(strict_types=1);

namespace App\Dto\View\MemberParticipation;

use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class MemberActivitiesView implements TurboStreamViewInterface
{
    /**
     * @param MemberActivityView[] $activities
     */
    public function __construct(
        public int $memberId,
        public array $queries,
        public string $period,
        public ?string $type,
        public LinkView $action,
        public int $counter,
        public array $activities,
        public ?BadgeView $totalIndemnity,
    ) {
    }
}
