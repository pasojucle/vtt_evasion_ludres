<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\Filter\AbstractFilter;
use App\Dto\View\ListView;
use App\Service\Filter\FilterConfigInterface;

/**
 * @template T of object
 */
interface ListProviderInterface
{
    /**
     * @template TFilter of AbstractFilter
     * @param array $queryParameters
     * @param class-string<TFilter> $filterClass
     * @return TFilter
     */
    public function getHydratedDto(array $queryParameters, string $filterClass): AbstractFilter;

    /**
     * Summary of getFilterConfig
     * @param string $route
     */
    public function getFilterConfig(string $route): ?FilterConfigInterface;

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