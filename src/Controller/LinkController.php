<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Link;
use App\Repository\ContentRepository;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    #[Route('/liens', name: 'links', methods: ['GET'])]
    public function list(LinkRepository $linkRepository, ContentRepository $contentRepository): Response
    {
        return $this->render('link/list.html.twig', [
            'links' => $linkRepository->findByPosition(Link::POSITION_LINK_PAGE),
            'backgrounds' => $contentRepository->findOneByRoute('links')?->getBackgrounds(),
        ]);
    }
}
