<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\ProjectDirService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('wiki', name: 'wiki', methods: ['GET'])]
class WikiController extends AbstractController
{
    #[Route('/{directory}/{slug}', name: '_show', methods: ['GET'], defaults:['slug' => 'index'])]
    #[IsGranted('WIKI_VIEW')]
    public function show(ProjectDirService $projectDir, string $directory, string $slug): Response
    {
        $finder = new Finder();
        $finder->depth('==0')->notName(['index.md', 'img'])->in($projectDir->path('wiki'))->sortByName();
        $navBarre = [];
        foreach ($finder as $file) {
            $filename = $file->getFilenameWithoutExtension();
            $navBarre[] = ['directory' => $filename, 'title' => ucfirst(str_replace('-', ' ', $filename))];
        }

        $path = $projectDir->path('wiki', $directory, sprintf('%s.md', $slug));

        if (file_exists($path)) {
            return $this->render('wiki/admin/show.html.twig', [
                'content' => file_get_contents($path),
                'directory' => ucfirst(str_replace('-', ' ', $directory)),
                'nav_barre' => $navBarre,
            ]);
        }
        
        return new Response(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/img/{directory}/{filename}', name: '_img', methods: ['GET'])]
    #[IsGranted('WIKI_VIEW')]
    public function img(ProjectDirService $projectDir, string $directory, string $filename): Response
    {
        $path = $projectDir->path('wiki', $directory, 'img', $filename);
        if (file_exists($path)) {
            return new BinaryFileResponse($path);
        }
        
        return new Response(null, Response::HTTP_NOT_FOUND);
    }
}
