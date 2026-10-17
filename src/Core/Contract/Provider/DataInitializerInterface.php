<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

/**
 * @template T of object
 */
interface DataInitializerInterface
{
    /**
     * @param T $sourceData
     * @return T
     */
    public function initializeData(object $sourceData): object;
}
