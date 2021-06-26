<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
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
     * @Route("/admin/calendrier", name="admin_events")
     */
    public function adminList(
        PaginatorService $paginator,
        Request $request
    ): Response
    {
        $query =  $this->eventRepository->findAllQuery();
        $events =  $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('event/list.html.twig', [
            'events' => $events,
            'lastPage' => $paginator->lastPage($events)
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
