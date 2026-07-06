<?php

declare(strict_types=1);

namespace App\Dto\State;

readonly class JsonProcessorResult
{
    public function __construct(
        public bool $success,
        public array $data,
    ) {
    }
}
