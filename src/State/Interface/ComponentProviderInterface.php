<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\View\Interface\ComponentViewInterface;

/**
 * @template T of object
 */
interface ComponentProviderInterface
{
    /**
     * @param T $entity
     */
    public function getView(object $entity, ?string $fallback = null): ComponentViewInterface;
}
