<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Service\ProjectDirService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ZipArchive;

class GetTrackFile
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
        if ($mimeType === 'zip') {
            return $this->createZipResponse($path, $filename);
        }

        $response = new BinaryFileResponse($path);
        if ($mimeType === 'image') {
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);

            return $response;
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }

    private function createZipResponse(string $filePath, string $originalName): Response
    {
        $zipPath = sys_get_temp_dir() . '/' . uniqid() . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $zip->addFile($filePath, $originalName);
            $zip->close();
        }

        $response = new BinaryFileResponse($zipPath);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            str_replace('.gpx', '.zip', $originalName)
        );
        
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
