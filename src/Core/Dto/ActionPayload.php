<?php

declare(strict_types=1);

namespace App\Core\Dto;

use App\Core\Contract\PayloadInterface;

readonly class ActionPayload implements PayloadInterface
{
    public function __construct(
        public object $data,
        public array $files = [],
    ) {
    }
}
