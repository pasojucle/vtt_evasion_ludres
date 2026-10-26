<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\AutocompleteProviderInterface;
use App\Core\Contract\Provider\ListFilteredProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

readonly class AutocompleteHandler
{
    public function handle(
        Request $request,
        AutocompleteProviderInterface $provider,
        ?string $filterClass = null,
    ): JsonResponse {
        $filter = ($provider instanceof ListFilteredProviderInterface)
            ? $provider->getHydratedDto($request->query->all(), $filterClass)
            : null;

        $query = $request->query->get('query');

        return new JsonResponse(['results' => $provider->getAutocompleteChoices($query, $filter)]);
    }
}
