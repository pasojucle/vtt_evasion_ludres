<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;

interface FilterInitializerInterface
{
    public function initializeFilters(AbstractFilter $filter, ?HandlerContext $context): void;
}
