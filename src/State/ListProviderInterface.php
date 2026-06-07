<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\Filter\AbstractFilter;
use App\Dto\ListDto;
use App\Service\Filter\FilterConfigInterface;

/**
 * @template T of object
 */
interface ListProviderInterface
{
    public function getCollection(
        AbstractFilter $filter, 
        FilterConfigInterface $filterConfig, 
        string $route, 
        ?int $currentPage = 1
    ): ListDto;

    public function streamExportContent(AbstractFilter $filter): void;
}