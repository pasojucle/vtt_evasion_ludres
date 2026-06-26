<?php

declare(strict_types=1);

namespace App\Dto\State;

readonly class ProcessorResult
{
    public function __construct(
        public bool $success,
        public string $messageKey,
        public ?string $targetUrl = null,
        public string $flashType = 'success',
    ) {
    }
}
