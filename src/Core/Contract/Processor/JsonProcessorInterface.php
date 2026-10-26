<?php

declare(strict_types=1);

namespace App\Core\Contract\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\ProcessorInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\JsonProcessorResult;

/**
* @template TPayload of PayloadInterface
 */
interface JsonProcessorInterface extends ProcessorInterface
{
    /**
     * @param TPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): JsonProcessorResult;
}
