<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\Filter\AbstractFilter;

interface StreamExportableInterface
{
    /**
     * @template TFilter of AbstractFilter
     * @param array $queryParameters
     * @param class-string<TFilter> $filterClass
     * @return TFilter
     */
    public function getHydratedDto(array $queryParameters, string $filterClass): AbstractFilter;

    public function streamExportContent(AbstractFilter $filter): void;
}