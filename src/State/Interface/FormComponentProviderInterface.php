<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Core\Contract\View\ComponentFormViewInterface;
use App\Dto\State\ViewContext;

/**
 * @template T of object
 */
interface FormComponentProviderInterface
{
    /**
     * @param T $entity
     */
    public function getFormView(object $entity, ?ViewContext $context = null): ComponentFormViewInterface;

    /**
     * @param T $entity
     */
    public function getFormOptions(object $entity): array;
}
