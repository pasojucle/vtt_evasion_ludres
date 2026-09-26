<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\State\ViewContext;
use App\Dto\View\ListDrawerView;

/**
 * @template T of object
 */
interface ListDrawerProviderInterface extends ListFilteredProviderInterface
{
    /**
     * @param T $filter
     * @param ViewContext $context,
     */
    public function getCollection(AbstractFilter $filter, ViewContext $context): ListDrawerView;
}
