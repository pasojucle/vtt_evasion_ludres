<?php

declare(strict_types=1);

namespace App\State\Interface;

/**
 * @template T of object
 */
interface FormAddComponentProviderInterface extends FormComponentProviderInterface
{
    /**
     * @param T $entity
     * @return T
     */
    public function setDefaultValues(object $entity): object;
}
