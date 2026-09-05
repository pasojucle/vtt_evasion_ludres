<?php

declare(strict_types=1);

namespace App\Dto\View\Cluster;

use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\LinkView;

readonly class ParticipantView
{
    /**
     * @param BadgeView[] $indicators
     */
    public function __construct(
        public int $sessionId,
        public string $url,
        public bool $isPresent,
        public bool $isFramer,
        public int $userId,
        public string $fullName,
        public BadgeView $level,
        public ?BadgeView $levelType,
        public DropdownView $dropdown,
        public array $indicators,
        public ?LinkView $action,
        public ?BadgeView $status,
    ){}
}