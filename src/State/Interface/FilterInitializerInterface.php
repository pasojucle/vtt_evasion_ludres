<?php

declare(strict_types=1);

namespace App\State\Interface;

use App\Dto\Filter\AbstractFilter;

interface FilterInitializerInterface
{
    public function initializeFilters(AbstractFilter $filter, array $queryParams = []): void;
}
