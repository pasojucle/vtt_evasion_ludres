<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\TextEditorUpload;
use App\Service\FileLocation\FileLocationResolver;
use App\Service\FileLocation\TextEditorFileLocation;
use App\Service\FileService;
use App\Service\UploadService;
use Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FileController extends AbstractController
{
    #[Route('/file/{filename}', name: 'get_file', methods: ['GET', 'POST'])]
    public function getFile(
        string $filename
    ): Response {
        $filename = base64_decode($filename);

        if (file_exists($filename)) {
            return new BinaryFileResponse($filename);
        }

        return new Response(null, 204);
    }

    #[Route('/data/file/{directory}/{filename}', name: 'get_data_file', methods: ['GET', 'POST'])]
    public function getDataFile(
        FileLocationResolver $resolver,
        FileService $fileService,
        string $directory,
        string $filename
    ): Response {
        $pathDir = $resolver->resolveDirectory($directory)->getDirectory();
        if (!$pathDir) {
            throw $this->createNotFoundException('Le répertoire demandé n’est pas valide.');
        }
        
        $path = $fileService->join($pathDir, base64_decode($filename));

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Le fichier demandé n’est pas valide.');
        }

        return new BinaryFileResponse($path);
    }

    #[Route('/upload', name: 'upload_file', methods: ['GET', 'POST'])]
    public function uploadFile(
        Request $request,
        UploadService $uploadService,
        TextEditorFileLocation $location,
    ): JsonResponse {
        $file = $request->files->get('upload');
        if ($file) {
            $upload = new TextEditorUpload($file->getClientOriginalName());
            $filename = null;
            try {
                $filename = $uploadService->uploadFile($file, $upload);
                $directory = $location->getBaseDirectoryName();

                return new JsonResponse(['url' => $this->generateUrl('get_data_file', ['directory' => $directory, 'filename' => base64_encode($filename)])]);
            } catch (Error $e) {
                return new JsonResponse(['error' => ['message' => $e->getMessage()]]);
            }
        }

        return new JsonResponse(['error' => ['message' => 'fichier manquant']]);
    }
}
