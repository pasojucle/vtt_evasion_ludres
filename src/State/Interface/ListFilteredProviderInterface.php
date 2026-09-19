<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Dto\Filter\AbstractFilter;

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
     * @param string $route
     */
    public function getFilterConfig(string $route): ?FilterConfigInterface;
}
