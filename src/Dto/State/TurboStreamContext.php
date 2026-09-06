<?php

declare(strict_types=1);

namespace App\Dto\State;

readonly class TurboStreamContext
{
    public function __construct(
        public string $route,
        public array $routeParam,
        public int $page,
        public ?object $object = null,
    ) {
    }
}
