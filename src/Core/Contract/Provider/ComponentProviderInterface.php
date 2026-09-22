<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Dto\HandlerContext;

/**
 * @template T of object
 */
interface ComponentProviderInterface
{
    /**
     * @param T $data
     */
    public function getView(object $data, ?HandlerContext $context = null): ComponentViewInterface;
}
