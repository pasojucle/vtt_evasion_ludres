<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Session;
use App\Form\Admin\EventType;
use App\Repository\EventRepository;
use App\Repository\LevelRepository;
use App\Repository\ParameterRepository;
use App\Repository\SessionRepository;
use App\Service\EventService;
use App\Service\FilenameService;
use App\Service\PaginatorService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class BikeRideController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private EventService $eventService
    ) {
    }

    #[Route('/', name: 'admin_home', methods: ['GET'])]
    public function adminHome()
    {
        return $this->redirectToRoute('admin_events');
    }

    #[Route('/calendrier/{period}/{year}/{month}/{day}', name: 'admin_events', methods: ['GET', 'POST'], defaults:['period' => null, 'year' => null, 'month' => null, 'day' => null])]
    public function adminList(
        PaginatorService $paginator,
        Request $request,
        ?string $period,
        ?int $year,
        ?int $month,
        ?int $day
    ): Response {
        $response = $this->eventService->getSchedule($request, $period, $year, $month, $day);

        if (array_key_exists('redirect', $response)) {
            return $this->redirectToRoute($response['redirect'], $response['filters']);
        }

        return $this->render('bike_ride/admin/list.html.twig', $response['parameters']);
    }
    
    #[Route('/sortie/{event}', name: 'admin_event_edit', methods: ['GET', 'POST'], defaults:['event' => null])]
    public function adminEdit(
        Request $request,
        LevelRepository $levelRepository,
        ParameterRepository $parameterRepository,
        ?Event $event
    ): Response {
        if (null === $event) {
            $event = new Event();
        }
        $event = $this->eventService->setDefaultContent($request, $event);
        $filters = $this->requestStack->getSession()->get('admin_events_filters');
        $form = $this->createForm(EventType::class, $event);

        if (!$request->isXmlHttpRequest()) {
            $form->handleRequest($request);
        }
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            $clusters = $event->getClusters();
            if ($clusters->isEmpty($event)) {
                $this->eventService->createClusters($event);
            }

            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->addFlash('success', 'La sortie à bien été enregistrée');

            $filters = $this->eventService->getFilters(Event::PERIOD_MONTH, $event->getStartAt());

            return $this->redirectToRoute('admin_events', $filters);
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'events_filters' => ($filters) ? $filters : [],
        ]);
    }
   
    #[Route('/sortie/groupe/{event}', name: 'admin_event_cluster_show', methods: ['GET'])]
    public function adminClusterShow(
        Request $request,
        EventService $eventService,
        Event $event
    ): Response {
        $filters = $this->requestStack->getSession()->get('admin_events_filters');
        $this->requestStack->getSession()->set('user_return', $this->generateUrl('admin_event_cluster_show', [
            'event' => $event->getId(),
        ]));

        return $this->render('event/cluster_show.html.twig', [
            'event' => $eventService->getEventWithPresentsByCluster($event),
            'events_filters' => ($filters) ? $filters : [],
        ]);
    }
    
    #[Route('/sortie/export/{event}', name: 'admin_event_export', methods: ['GET', 'POST'], defaults:[])]
    public function adminEventExport(
        SessionRepository $sessionRepository,
        UserService $userService,
        FilenameService $filenameService,
        Event $event
    ): Response {
        $sessions = $sessionRepository->findByEvent($event);
        $separator = ',';
        $fileContent = [];
        $fileContent[] = $event->getTitle() . ' - ' . $event->getStartAt()->format('d/m/Y');
        $fileContent[] = '';
        $row = ['n° de Licence', 'Nom', 'Prénom', 'Présent', 'Niveau'];
        $fileContent[] = implode($separator, $row);
        if (!empty($sessions)) {
            foreach ($sessions as $session) {
                if (Session::AVAILABILITY_UNAVAILABLE !== $session->getAvailability()) {
                    $user = $userService->convertToUser($session->getUser());
                    $member = $user->getMember();
                    $present = ($session->isPresent()) ? 'oui' : 'non';
                    $row = [$user->getLicenceNumber(), $member['name'], $member['firstName'], $present, $user->getLevel()];
                    $fileContent[] = implode($separator, $row);
                }
            }
        }
        $filename = $event->getTitle() . '_' . $event->getStartAt()->format('Y_m_d');
        $filename = $filenameService->clean($filename) . '.csv';
        $response = new Response(implode(PHP_EOL, $fileContent));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename,
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
