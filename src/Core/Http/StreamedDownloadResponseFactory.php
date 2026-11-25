<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Contract\Provider\StreamDownloadProviderInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class StreamedDownloadResponseFactory
{
    public function create(
        object $data,
        StreamDownloadProviderInterface $provider,
        string $filename,
        string $contentType,
    ): StreamedResponse {
        $response = new StreamedResponse(function () use ($provider, $data) {
            $provider->streamDownloadContent($data);
        });
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s"', addslashes($filename)),
        );

        return $response;
    }
}
