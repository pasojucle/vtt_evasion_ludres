<?php

declare(strict_types=1);

namespace App\Dto;

class RouteDto
{
    public function __construct(
        public string $name,
        public array $params = [],
    ) {
    }
}
