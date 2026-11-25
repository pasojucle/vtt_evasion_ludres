<?php

declare(strict_types=1);

namespace App\Core\Handler;

use App\Core\Contract\Provider\StreamDownloadProviderInterface;
use App\Core\Http\StreamedDownloadResponseFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class ActionExportHandler
{
    public function __construct(
        private StreamedDownloadResponseFactory $streamedDownloadResponseFactory,
    ) {
    }

    public function handle(
        StreamDownloadProviderInterface $provider,
        object $data
    ): StreamedResponse {
        return $this->streamedDownloadResponseFactory->create(
            $data,
            $provider,
            $provider->basename($data),
            $provider->contentType(),
        );
    }
}
