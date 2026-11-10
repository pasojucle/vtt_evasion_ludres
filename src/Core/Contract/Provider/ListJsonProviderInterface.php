<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;

/**
 * @template TFilter of AbstractFilter
 */
interface ListJsonProviderInterface
{
    /**
     * @param array $queryParameters
     * @param class-string<TFilter> $filterClass
     * @return TFilter
     */
    public function getHydratedDto(array $queryParameters, string $filterClass): AbstractFilter;

    /**
     * @param TFilter $filter
     */
    public function getJson(AbstractFilter $filter, ?HandlerContext $context = null): array;
}
