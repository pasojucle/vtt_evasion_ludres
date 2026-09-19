<?php

declare(strict_types=1);

namespace App\Core\Contract\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\HtmlProcessorResultInterface;

interface ProcessorInterface
{
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): HtmlProcessorResultInterface;
}
