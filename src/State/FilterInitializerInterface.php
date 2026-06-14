<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\Filter\AbstractFilter;

interface FilterInitializerInterface
{
    public function initializeFilters(AbstractFilter $filter): void;
}