<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\DialogType;

readonly class DialogModalDto
{

    public function __construct(
        public DialogType $type,
        public string $title,
        public string $action,
        public string $message,
        public string $icon
    )
    {

    }
}