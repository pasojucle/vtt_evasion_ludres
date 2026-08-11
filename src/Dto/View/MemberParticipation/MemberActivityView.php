<?php

declare(strict_types=1);

namespace App\Dto\View\MemberParticipation;


use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TurboStreamViewInterface;


readonly class MemberActivityView implements TurboStreamViewInterface
{
    public function __construct(
        public string $period,
        public string $title,
        public BadgeView $practice,
        public ?BadgeView $indemnity,
    ) {
    }
}
