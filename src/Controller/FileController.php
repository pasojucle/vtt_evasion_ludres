<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
