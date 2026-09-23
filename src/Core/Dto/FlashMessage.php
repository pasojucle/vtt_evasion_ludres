<?php

declare(strict_types=1);

namespace App\Core\Dto;

readonly class FlashMessage
{
    public function __construct(
        public string $type,
        public string $messageKey,
    ) {
    }
}
