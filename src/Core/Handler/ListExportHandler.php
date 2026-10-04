<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\State\Interface\StreamListExportableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class ListExportHandler
{
    public function handle(
        Request $request,
        string $filterClass,
        StreamListExportableInterface $provider,
        string $filename,
    ): StreamedResponse {
        $filter = $provider->getHydratedDto($request->query->all(), $filterClass);

        $response = new StreamedResponse(function () use ($provider, $filter) {
            $provider->streamExportContent($filter);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;
    }
}
