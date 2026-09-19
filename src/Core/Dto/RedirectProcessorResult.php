<?php

declare(strict_types=1);

namespace App\Core\Dto;

readonly class RedirectProcessorResult implements HtmlProcessorResultInterface
{
    public function __construct(
        public bool $success,
        public string $messageKey,
        public string $targetUrl,
        public string $flashType = 'success',
    ) {
    }
}
