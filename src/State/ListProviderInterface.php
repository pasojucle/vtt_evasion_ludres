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
     * @template T of AbstractFilter
     * @param array $queryParameters
     * @param class-string<T> $filterClass
     * @return T
     */
    public function getHydratedDto(array $queryParameters, string $filterClass): AbstractFilter;

    /**
     * Summary of getFilterConfig
     * @param string $route
     * @return void
     */
    public function getFilterConfig(string $route): ?FilterConfigInterface;

    /**
     * Summary of getCollection
     * @param AbstractFilter $filter
     * @param FilterConfigInterface $filterConfig
     * @param string $route
     * @param ?int $currentPage
     * @return void
     */
    public function getCollection(
        AbstractFilter $filter, 
        FilterConfigInterface $filterConfig, 
        string $route, 
        ?int $currentPage = 1,
    ): ListView;
}