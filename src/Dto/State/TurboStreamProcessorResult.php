<?php

declare(strict_types=1);

namespace App\Dto\State;

readonly class TurboStreamProcessorResult implements HtmlProcessorResultInterface
{
    public function __construct(
        public bool $success,
        public string $messageKey,
        public string $flashType = 'success',
    ) {
    }
}
