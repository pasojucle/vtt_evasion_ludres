<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Contract\View\TurboStreamViewInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;

/**
 * @template T of object
 */
interface TurboStreamUpdateProviderInterface
{
    /**
     * @param T $data
     */
    public function getUpdateStreamView(object $data, ?HandlerContext $context = null): TurboStreamViewInterface;
}
