<?php

declare(strict_types=1);

namespace App\Dto\State;

use App\Dto\Filter\AbstractFilter;

readonly class ViewContext
{
    public function __construct(
        public string $route,
        public array $routeParams = [],
        public ?AbstractFilter $filters = null,
        public int $page = 1,
        public int $tab = 1,
        public ?object $parent = null,
        public ?string $encodedFallback = null,
        public ?string $fallback = null,
    ) {
    }
}
