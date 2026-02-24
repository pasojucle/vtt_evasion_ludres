<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Documentation;
use App\Entity\User;
use App\Repository\DocumentationRepository;
use App\Service\LogService;
use App\Service\ProjectDirService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocumentationController extends AbstractController
{
    #[Route('/documentation/show/{documentation}', name: 'documentation_show', methods: ['GET'])]
    public function show(
        Documentation $documentation,
        ProjectDirService $projectDirService,
        LogService $logService,
    ): Response {
        $path = $projectDirService->path('documentation', $documentation->getFilename());
        if (file_exists($path)) {
            $logService->writeFromEntity($documentation);
            return new BinaryFileResponse($path);
        }

        return new Response(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/documentation/novelty/{documentation}', name: 'documentation_novelty', methods: ['GET'])]
    public function novelty(
        Documentation $documentation,
        DocumentationRepository $documentationRepository,
    ): Response {
        /** @var ?User $user */
        $user = $this->getUser();

        return $this->render('documentation/_frame_novelty.html.twig', [
            'documentation' => $documentation,
            'isNovelty' => ($user)
                ? $documentationRepository->isNoveltyByUser($user, $documentation)
                : false
        ]);
    }
}
