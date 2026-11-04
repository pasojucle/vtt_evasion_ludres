<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\StreamListExportableInterface;
use App\Core\Dto\HandlerContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class ListExportHandler
{
    public function handle(
        Request $request,
        string $filterClass,
        StreamListExportableInterface $provider,
        ?object $parent = null,
    ): StreamedResponse {
        $context = HandlerContext::fromRequest($request, $parent);
        $filter = $provider->getHydratedDto($context->queryParams, $filterClass);
        $filename = $provider->filename($context);

        $response = new StreamedResponse(function () use ($provider, $filter, $context) {
            $provider->streamExportContent($filter, $context);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;
    }
}
