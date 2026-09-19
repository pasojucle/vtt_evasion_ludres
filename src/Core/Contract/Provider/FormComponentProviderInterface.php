<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Contract\View\ComponentFormViewInterface;
use App\Core\Dto\HandlerContext;

/**
 * @template T of object
 */
interface FormComponentProviderInterface
{
    /**
     * @param T $data
     */
    public function getView(object $data, ?HandlerContext $context = null): ComponentFormViewInterface;

    /**
     * @param T $data
     */
    public function getFormOptions(object $data): array;
}
