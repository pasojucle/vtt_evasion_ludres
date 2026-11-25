<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\JsonProviderInterface;
use App\Core\Contract\Provider\ListFilteredProviderInterface;
use App\Core\Dto\HandlerContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

readonly class JsonHandler
{
    public function handle(
        Request $request,
        JsonProviderInterface $provider,
        ?string $filterClass,
        ?object $parent = null,
    ): JsonResponse {
        $context = HandlerContext::fromRequest($request, $parent);
        $filter = ($provider instanceof ListFilteredProviderInterface)
           ? $provider->getHydratedDto($context->queryParams, $filterClass)
           : null;
           
        return new JsonResponse($provider->getJson($filter, $context));
    }
}
