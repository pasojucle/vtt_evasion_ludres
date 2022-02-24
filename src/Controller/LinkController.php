<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Link;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    public function __construct(
        private LinkRepository $linkRepository,
    ) {
    }

    #[Route('/liens', name: 'links', methods: ['GET'])]
    public function list(): Response {
        $links = $this->linkRepository->findByPosition(Link::POSITION_LINK_PAGE);

        return $this->render('link/list.html.twig', [
            'links' => $links,
        ]);
    }
}
