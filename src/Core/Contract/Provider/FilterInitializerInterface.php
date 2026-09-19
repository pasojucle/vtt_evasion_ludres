<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Dto\Filter\AbstractFilter;

interface FilterInitializerInterface
{
    public function initializeFilters(AbstractFilter $filter, array $queryParams = []): void;
}
