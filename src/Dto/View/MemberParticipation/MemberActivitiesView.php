<?php

declare(strict_types=1);

namespace App\Dto\View\MemberParticipation;


readonly class MemberActivitiesView
{
    /**
     * @param MemberActivityView[] $activities
     */
    public function __construct(
        public int $memberId,
        public int $counter,
        public array $activities,
    ) {
    }
}
