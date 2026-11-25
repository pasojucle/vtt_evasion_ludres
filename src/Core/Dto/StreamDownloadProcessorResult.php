<?php

declare(strict_types=1);

namespace App\Core\Dto;

readonly class StreamDownloadProcessorResult implements HtmlProcessorResultInterface
{
    public function __construct(
        public object $data,
    ) {
    }
}
