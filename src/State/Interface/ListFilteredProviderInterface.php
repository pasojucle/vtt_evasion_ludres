<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\Filter\AbstractFilter;
use App\Service\Filter\FilterConfigInterface;

/**
 * @template T of object
 */
interface ListFilteredProviderInterface
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
}
