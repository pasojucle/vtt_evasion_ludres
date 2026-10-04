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
        string $filename,
    ): StreamedResponse {

        $response = new StreamedResponse(function () use ($provider, $data, $filename) {
            $provider->streamExportContent($data, $filename);
        });
        $response->headers->set('Content-Type', 'appication/pdf; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;
    }
}
