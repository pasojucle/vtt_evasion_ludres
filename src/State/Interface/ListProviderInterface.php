<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\Filter\AbstractFilter;
use App\Dto\View\ListView;
use App\Service\Filter\FilterConfigInterface;

/**
 * @template T of object
 */
interface ListProviderInterface extends ListFilteredProviderInterface
{
    /**
     * Summary of getCollection
     * @param AbstractFilter $filter
     * @param FilterConfigInterface $filterConfig
     * @param string $route
     * @param ?int $currentPage
     */
    public function getCollection(
        AbstractFilter $filter,
        FilterConfigInterface $filterConfig,
        string $route,
        ?int $currentPage = 1,
    ): ListView;
}
