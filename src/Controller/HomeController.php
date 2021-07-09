<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home (
        LinkRepository $linkRepository,
        EventRepository $eventRepository
    ): Response
    {
        $links = $linkRepository->findHomePageView();
        $events = $eventRepository->findEnableView();

        return $this->render('home/index.html.twig', [
            'links' => $links,
            'events' => $events,
        ]);
    }
}
