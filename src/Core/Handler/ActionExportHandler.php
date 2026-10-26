<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\ListFilteredProviderInterface;
use App\Core\Contract\Provider\StreamActionExportableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class ActionExportHandler
{
    public function handle(
        Request $request,
        StreamActionExportableInterface $provider,
        ?object $data = null,
        ?string $filterClass = null,
    ): StreamedResponse {
        $filter = ($provider instanceof ListFilteredProviderInterface)
            ? $provider->getHydratedDto($request->query->all(), $filterClass)
            : null;

        $basename = $provider->basename($data);
        $response = new StreamedResponse(function () use ($provider, $data, $filter) {
            $provider->streamExportContent($data, $filter);
        });
        $response->headers->set('Content-Type', 'application/pdf; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $basename);

        return $response;
    }
}
