<?php

declare(strict_types=1);

namespace App\Dto\State;

readonly class TurboStreamProcessorResult implements HtmlProcessorResultInterface
{
    public function __construct(
        public string $laziTemplate,
    ) {
    }
}
