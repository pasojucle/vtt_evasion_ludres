<?php

declare(strict_types=1);

namespace App\Core\Contract\Provider;

use App\Dto\Filter\AbstractFilter;

/**
 * @template TFilter of ?AbstractFilter
 */
interface AutocompleteProviderInterface
{
    /**
     * @param TFilter $filter
     * @return list<array{value: int|string, text: string}>
     */
    public function getAutocompleteChoices(?string $query, ?AbstractFilter $filter): array;
}
