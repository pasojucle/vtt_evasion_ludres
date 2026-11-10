<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\ListJsonProviderInterface;
use App\Core\Dto\HandlerContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

readonly class ListJsonHandler
{
    public function handle(
        Request $request,
        ListJsonProviderInterface $provider,
        string $filterClass,
        ?object $parent = null,
    ): JsonResponse {
        $context = HandlerContext::fromRequest($request, $parent);
        $filter = $provider->getHydratedDto($context->queryParams, $filterClass);
        
        return new JsonResponse($provider->getJson($filter));
    }
}
