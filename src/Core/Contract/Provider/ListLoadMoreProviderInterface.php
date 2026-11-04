<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Contract\View\LoadMoreViewInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;

/**
 * @template T of object
 */
interface ListLoadMoreProviderInterface extends ListFilteredProviderInterface, FormComponentProviderInterface
{
    /**
     * @param T $data
     */
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): LoadMoreViewInterface;
}
