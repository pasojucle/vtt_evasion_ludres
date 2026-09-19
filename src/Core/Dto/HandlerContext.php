<?php

declare(strict_types=1);

namespace App\Core\Dto;

use Symfony\Component\HttpFoundation\Request;

readonly class HandlerContext
{
    public function __construct(
        public ?string $route,
        public array $routeParams,
        public array $queryParams,
        public int $page,
        public int $tab,
        public ?string $encodedFallback,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            route: $request->attributes->get('_route'),
            routeParams: $request->attributes->get('_route_params', []),
            queryParams: $request->query->all(),
            page: $request->query->getInt('page', 1),
            tab: $request->query->getInt('tab', 1),
            encodedFallback: $request->query->get('_redirect_to') ?? $request->request->get('_redirect_to'),
        );
    }
}
