<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Service\ProjectDirService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ZipArchive;

class GetBikeRideFile
{
    public function __construct(
        private ProjectDirService $projectDirService,
    ) {
    }

    public function execute(string $filename, string $mimeType): Response
    {
        $filename = base64_decode($filename);
        $path = $this->projectDirService->path('bike_ride_track', $filename);
        if (!file_exists($path)) {
            $path = $this->projectDirService->dir('upload', $filename);
        }

        if (!file_exists($path)) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);

        return $response;
    }
}
