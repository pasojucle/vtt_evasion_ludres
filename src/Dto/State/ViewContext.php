<?php

declare(strict_types=1);

namespace App\Dto\State;

readonly class ViewContext
{
    public function __construct(
        public string $route,
        public array $routeParams,
        public int $page = 1,
        public ?object $parent = null,
        public ?string $fallback = null,
    ) {
    }
}
