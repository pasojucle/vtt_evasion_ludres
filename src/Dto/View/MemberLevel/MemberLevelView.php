<?php

declare(strict_types=1);

namespace App\Dto\View\MemberLevel;

use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TurboStreamViewInterface;
use App\Dto\View\LinkView;

readonly class MemberLevelView implements TurboStreamViewInterface
{
    public function __construct(
        public int $id,
        public BadgeView $level,
        public string $levelType,
        public LinkView $action,
    ) {
    }
}
