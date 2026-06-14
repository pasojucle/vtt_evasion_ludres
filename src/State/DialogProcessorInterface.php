<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\State\ProcessorResult;

/**
 * @template T of object
 */
interface DialogProcessorInterface
{
    /**
     * @param T $entity
     */
    public function process(object $entity, ?string $filter): ProcessorResult;
}