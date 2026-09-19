<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Core\Contract\View\LoadMoreViewInterface;
use App\Dto\State\ViewContext;

interface ListLoadMoreProviderInterface extends ListFilteredProviderInterface, TurboStreamProviderInterface, FormComponentProviderInterface
{
    public function getStreamView(object $entity, ?ViewContext $context = null): LoadMoreViewInterface;
}
