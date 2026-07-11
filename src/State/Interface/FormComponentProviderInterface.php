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
    public function mapToView(object $entity): ComponentFormViewInterface;
}
