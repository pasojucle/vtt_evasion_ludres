<?php

namespace App\Controller;

use App\Entity\Link;
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
        $linksBikeRide = $linkRepository->findByPosition(Link::POSITION_HOME_BIKE_RIDE);
        $linksFooter = $linkRepository->findByPosition(Link::POSITION_HOME_FOOTER);
        $events = $eventRepository->findEnableView();

        return $this->render('home/index.html.twig', [
            'links_bike_ride' => $linksBikeRide,
            'links_footer' => $linksFooter,
            'events' => $events,
        ]);
    }
}
