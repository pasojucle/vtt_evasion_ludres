<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\StreamActionExportableInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class ActionExportHandler
{
    public function handle(
        object $data,
        StreamActionExportableInterface $provider,
    ): StreamedResponse {
        $basename = $provider->basename($data);
        $response = new StreamedResponse(function () use ($provider, $data) {
            $provider->streamExportContent($data);
        });
        $response->headers->set('Content-Type', 'application/pdf; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $basename);

        return $response;
    }
}
