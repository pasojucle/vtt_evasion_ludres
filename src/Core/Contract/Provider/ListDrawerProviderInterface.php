<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;
use App\Dto\View\ListDrawerView;

/**
 * @template T of AbstractFilter
 */
interface ListDrawerProviderInterface extends ListFilteredProviderInterface
{
    /**
     * @param T $filter
     * @param HandlerContext $context,
     */
    public function getCollection(AbstractFilter $filter, HandlerContext $context): ListDrawerView;
}
