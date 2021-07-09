<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Event;
use App\Form\EventType;
use App\Form\EventFilterType;
use App\Service\BikeRideService;
use App\Service\PaginatorService;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;
    private BikeRideService $bikeRideService;

    public function __construct(
        EventRepository $eventRepository,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        BikeRideService $bikeRideService
    )
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->bikeRideService = $bikeRideService;
    }

    
    /**
     * @Route(
     *  "/admin/",
     *  name="admin_home")
     */
    public function adminHome()
    {
        return $this->redirectToRoute('admin_events');
    }

    /**
     * @Route(
     *  "/admin/calendrier/{period}/{year}/{month}/{day}",
     *  name="admin_events",
     *  defaults={"period"=null, "year"=null, "month"=null, "day"=null})
     */
    public function adminList(
        PaginatorService $paginator,
        Request $request,
        ?string $period,
        ?int $year,
        ?int $month,
        ?int $day
    ): Response
    {
        $response = $this->bikeRideService->getBikeRideList($request, $period, $year, $month, $day);

        if (array_key_exists('redirect', $response)) {
            return $this->redirectToRoute($response['redirect'], $response['filters']);
        }
        return $this->render('bike_ride/admin/list.html.twig', $response['parameters']);
    }

    /**
     * @Route("/admin/randonnee/{event}", name="admin_event_edit", defaults={"event"=null})
     */
    public function adminEdit(
        Request $request,
        ?Event $event
    ): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $this->entityManager->persist($event);
            $this->entityManager->flush();
        }
        $filters = $this->session->get('admin_events_filters');
        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'events_filters' => ($filters) ? $filters : [],
        ]);
    }

    /**
     * @Route("/admin/randonnee/groupe/{event}", name="admin_event_cluster_show")
     */
    public function adminClusterShow(
        Request $request,
        Event $event
    ): Response
    {
        return $this->render('event/cluster_show.html.twig', [
            'event' => $event,
            'events_filters' => $this->session->get('admin_events_filters'),
        ]);
    }

    /**
     * @Route(
     *  "/randonnees/{period}/{year}/{month}/{day}",
     *  name="bike_rides",
     *  defaults={"period"=null, "year"=null, "month"=null, "day"=null})
     */
    public function list(
        PaginatorService $paginator,
        Request $request,
        ?string $period,
        ?int $year,
        ?int $month,
        ?int $day
    ): Response
    {
        $response = $this->bikeRideService->getBikeRideList($request, $period, $year, $month, $day);

        if (array_key_exists('redirect', $response)) {
            return $this->redirectToRoute($response['redirect'], $response['filters']);
        }
        return $this->render('bike_ride/list.html.twig', $response['parameters']);
    }
}
