<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\Interface\LoadMoreViewInterface;

interface ListLoadMoreProviderInterface extends ListFilteredProviderInterface, TurboStreamProviderInterface, FormComponentProviderInterface
{
    public function getStreamView(object $entity, ?TurboStreamContext $context = null): LoadMoreViewInterface;
}
