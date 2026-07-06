<?php

declare(strict_types=1);

namespace App\Dto\State;

readonly class HtmlProcessorResult
{
    public function __construct(
        public bool $success,
        public string $messageKey,
        public string $targetUrl,
        public string $flashType = 'success',
    ) {
    }
}
