<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Core\Contract\View\ComponentViewInterface;
use App\Dto\State\ViewContext;

/**
 * @template T of object
 */
interface ComponentProviderInterface
{
    /**
     * @param T $entity
     */
    public function getView(object $entity, ?ViewContext $context = null): ComponentViewInterface;
}
