<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\View\ComponentViewInterface;

/**
 * @template T of object
 */
interface FormComponentProviderInterface
{
    /**
     * @param T $entity
     */
    public function mapToView(object $entity): ComponentViewInterface;
}
