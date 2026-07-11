<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\State\JsonProcessorResult;

/**
 * @template T of object
 */
interface JsonProcessorInterface
{
    /**
     * @param T $object
     */
    public function process(object $object): JsonProcessorResult;
}
