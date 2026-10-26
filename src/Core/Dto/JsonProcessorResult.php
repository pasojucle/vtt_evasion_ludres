<?php

declare(strict_types=1);

namespace App\Core\Dto;

readonly class JsonProcessorResult implements HtmlProcessorResultInterface
{
    public function __construct(
        public bool $success,
        public array $data = [],
    ) {
    }
}
