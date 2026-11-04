<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;
use TFilter;


/**
 * @template TFilter of object
 */
interface StreamListExportableInterface
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
    public function streamExportContent(AbstractFilter $filter, ?HandlerContext $context = null): void;

    public function filename(?HandlerContext $context = null): string;
}
