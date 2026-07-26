<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\View\Interface\ComponentFormViewInterface;

/**
 * @template T of object
 */
interface FormComponentProviderInterface
{
    /**
     * @param T $entity
     */
    public function getFormView(object $entity, ?string $fallback = null): ComponentFormViewInterface;

    /**
     * @param T $entity
     */
    public function getFormOptions(object $entity): array;
}
