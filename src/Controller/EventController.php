<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Event;
use App\Form\EventType;
use App\Form\EventFilterType;
use App\Service\EventService;
use App\Service\PaginatorService;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(
     *  "/admin/calendrier/{period}/{year}/{month}/{day}",
     *  name="admin_events",
     *  defaults={"period"=null, "year"=null, "month"=null, "day"=null})
     */
    public function adminList(
        PaginatorService $paginator,
        EventService $eventService,
        Request $request,
        ?string $period,
        ?int $year,
        ?int $month,
        ?int $day
    ): Response
    {
        $filters = $eventService->getFiltersByParam($period, $year, $month, $day);
        $form = $this->createForm(EventFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($form->get('next')->isClicked()) {
                $data['direction'] = Event::DIRECTION_NEXT;
            }
            if ($form->get('prev')->isClicked()) {
                $data['direction'] = Event::DIRECTION_PREV;
            }
            if ($form->get('today')->isClicked()) {
                $today = new DateTime();
                $data['date'] = $today->format('Y-m-d');
            }
            $filters = $eventService->getFiltersByData($data);
            $form = $this->createForm(EventFilterType::class, $filters);
        }

        $query =  $this->eventRepository->findAllQuery($filters);
        $events =  $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('event/list.html.twig', [
            'form' => $form->createView(),
            'events' => $events,
            'lastPage' => $paginator->lastPage($events),
            'filters' => $filters,
        ]);
    }

    /**
     * @Route("/admin/sortie/{event}", name="admin_event_edit", defaults={"event"=null})
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
        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
