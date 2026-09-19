<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;
use App\Dto\View\ListView;

/**
 * @template T of object
 */
interface ListProviderInterface extends ListFilteredProviderInterface
{
    /**
     * Summary of getCollection
     * @param AbstractFilter $filter
     * @param FilterConfigInterface $filterConfig
     * @param HandlerContext $context
     */
    public function getCollection(
        AbstractFilter $filter,
        FilterConfigInterface $filterConfig,
        HandlerContext $context,
    ): ListView;
}
