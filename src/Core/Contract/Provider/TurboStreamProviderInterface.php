<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Contract\View\TurboStreamViewInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;

/**
 * @template T of object
 */
interface TurboStreamProviderInterface
{
    /**
     * @param T $data
     */
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): TurboStreamViewInterface;
}
