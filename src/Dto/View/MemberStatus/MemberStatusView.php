<?php

declare(strict_types=1);

namespace App\Dto\View\MemberStatus;

use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;


readonly class MemberStatusView implements TurboStreamViewInterface
{
    /**
     * @param BadgeView[] $permissions
     */
    public function __construct(
        public int $id,
        public BadgeView $level,
        public string $levelType,
        public string $boardRole,
        public array $permissions,
        public LinkView $action,
    ) {
    }
}
