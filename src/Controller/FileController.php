<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ProjectDirService;
use App\Service\UploadService;
use Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    #[Route('/file/{filename}', name: 'get_file', methods: ['GET', 'POST'])]
    public function getFile(
        string $filename
    ): Response {
        $filename = base64_decode($filename);
        dump($filename);

        if (file_exists($filename)) {
            return new BinaryFileResponse($filename);
        }

        return new Response(null, 204);
    }

    #[Route('/data/file/{directory}/{filename}', name: 'get_data_file', methods: ['GET', 'POST'])]
    public function getDataFile(
        ProjectDirService $projectDir,
        string $directory,
        string $filename
    ): Response {
        $path = $projectDir->path($directory, base64_decode($filename));

        if (file_exists($path)) {
            return new BinaryFileResponse($path);
        }

        return new Response(null, 204);
    }

    #[Route('/upload/{directory}', name: 'upload_file', methods: ['GET', 'POST'])]
    public function uploadFile(Request $request, UploadService $uploadService, ProjectDirService $projectDir, string $directory): JsonResponse
    {
        $file = $request->files->get('upload');
        if ($file) {
            $filename = null;
            try {
                $filename = $uploadService->uploadFile($file, $directory);
                return new JsonResponse(['url' => $this->generateUrl('get_data_file', ['directory' => $directory, 'filename' => base64_encode($filename)])]);
            } catch (Error $e) {
                return new JsonResponse(['error' => ['message' => $e->getMessage()]]);
            }
        }

        return new JsonResponse(['error' => ['message' => 'fichier manquant']]);
    }
}
