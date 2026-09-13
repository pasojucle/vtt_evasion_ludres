<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\Filter\AbstractFilter;
use App\Dto\State\ViewContext;
use App\Dto\View\ListDrawerView;

/**
 * @template T of object
 */
interface ListDrawerProviderInterface extends ListFilteredProviderInterface
{
    /**
     * Summary of getCollection
     * @param AbstractFilter $filter
     * @param ViewContext $context,
     */
    public function getCollection(
        AbstractFilter $filter,
        ViewContext $context,
    ): ListDrawerView;
}
