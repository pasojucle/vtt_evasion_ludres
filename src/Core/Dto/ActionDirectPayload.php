<?php

declare(strict_types=1);

namespace App\Core\Dto;

use App\Core\Contract\PayloadInterface;

readonly class ActionDirectPayload implements PayloadInterface
{
    public function __construct(
        public object $data,
        public ?string $token,
    ) {
    }
}
