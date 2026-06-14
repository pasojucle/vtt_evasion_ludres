<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\Enum\DialogType;

readonly class DialogModalView
{
    public function __construct(
        public DialogType $type,
        public string $title,
        public string $action,
        public string $message,
        public string $icon
    ) {
    }
}
