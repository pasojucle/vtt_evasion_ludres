<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Core\Dto\HandlerContext;
use App\Dto\Filter\AbstractFilter;

/**
 * @template TFilter of AbstractFilter
 */
interface JsonProviderInterface
{
    /**
     * @param TFilter $filter
     */
    public function getJson(?AbstractFilter $filter = null, ?HandlerContext $context = null): array;
}
