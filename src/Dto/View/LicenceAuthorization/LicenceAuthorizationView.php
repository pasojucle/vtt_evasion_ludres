<?php

declare(strict_types=1);

namespace App\Dto\View\LicenceAuthorization;

use App\Dto\View\BadgeView;
use App\Dto\View\Interface\TurboStreamViewInterface;

readonly class LicenceAuthorizationView implements TurboStreamViewInterface
{
    public function __construct(
        public int $id,
        public string $title,
        public string $label,
        public BadgeView $badge,
    ) {
    }
}
