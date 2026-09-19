<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

/**
 * @template T of object
 */
interface InputInitializerInterface
{
    /**
     * @param T $data
     * @return T
     */
    public function setDefaultValues(object $data): object;
}
