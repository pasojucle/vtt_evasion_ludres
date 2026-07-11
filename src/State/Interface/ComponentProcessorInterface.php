<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\State\ComponentProcessorResult;

/**
 * @template T of object
 */
interface ComponentProcessorInterface
{
    /**
     * @param T $object
     */
    public function process(object $object): ComponentProcessorResult;
}
