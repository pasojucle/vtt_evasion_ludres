<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Event;
use App\Entity\Session;
use App\Service\UserService;
use App\Form\Admin\EventType;
use App\Service\EventService;
use App\Service\PaginatorService;
use App\Repository\EventRepository;
use App\Repository\LevelRepository;
use App\Repository\SessionRepository;
use App\Repository\ParameterRepository;
use App\Service\FilenameService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private EventService $eventService;

    public function __construct(
        EventRepository $eventRepository,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        EventService $eventService
    )
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();
        $this->eventService = $eventService;
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
        $response = $this->eventService->getSchedule($request, $period, $year, $month, $day);

        if (array_key_exists('redirect', $response)) {
            return $this->redirectToRoute($response['redirect'], $response['filters']);
        }
        return $this->render('bike_ride/admin/list.html.twig', $response['parameters']);
    }

    /**
     * @Route("/admin/sortie/{event}", name="admin_event_edit", defaults={"event"=null})
     */
    public function adminEdit(
        Request $request,
        LevelRepository $levelRepository,
        ParameterRepository $parameterRepository,
        ?Event $event
    ): Response
    {
        if (null == $event) {
            $event = new Event();
        }
        $event = $this->eventService->setDefaultContent($request, $event);
        $filters = $this->session->get('admin_events_filters');
        $form = $this->createForm(EventType::class, $event);
      
        ;
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

    /**
     * @Route("/admin/sortie/groupe/{event}", name="admin_event_cluster_show")
     */
    public function adminClusterShow(
        Request $request,
        Event $event
    ): Response
    {
        $filters = $this->session->get('admin_events_filters');
        $this->session->set('user_return', $this->generateUrl('admin_event_cluster_show', ['event' => $event->getId()]));
        
        return $this->render('event/cluster_show.html.twig', [
            'event' => $event,
            'events_filters' =>  ($filters) ? $filters : [],
        ]);
    }

    /**
     * @Route("/admin/sortie/export/{event}", name="admin_event_export")
     */
    public function adminEventExport(
        SessionRepository $sessionRepository,
        UserService $userService,
        FilenameService $filenameService,
        Event $event
    ): Response
    {
        $sessions = $sessionRepository->findByEvent($event);
        $separator = ',';
        $fileContent = [];
        $fileContent[] = $event->getTitle().' - '.$event->getStartAt()->format('d/m/Y');
        $fileContent[] = '';
        $row = ['n° de Licence', 'Nom', 'Prénom', 'Présent', 'Niveau'];
        $fileContent[] = implode($separator, $row);
        if (!empty($sessions)) {
            foreach($sessions as $session) {
                if (Session::AVAILABILITY_UNAVAILABLE !== $session->getAvailability()) {
                    $user = $userService->convertToUser($session->getUser());
                    $member = $user->getMember();
                    $present = ($session->isPresent()) ? 'oui' : 'non';
                    $row = [$user->getLicenceNumber(), $member['name'], $member['firstName'], $present, $user->getLevel()];
                    $fileContent[] = implode($separator, $row);
                }
            }
        }
        $filename = $event->getTitle().'_'.$event->getStartAt()->format('Y_m_d');
        $filename = $filenameService->clean($filename).'.csv';
        $response = new Response(implode(PHP_EOL, $fileContent));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename,
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route(
     *  "/programme/{period}/{year}/{month}/{day}",
     *  name="schedule",
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
        $response = $this->eventService->getSchedule($request, $period, $year, $month, $day);

        if (array_key_exists('redirect', $response)) {
            return $this->redirectToRoute($response['redirect'], $response['filters']);
        }

        return $this->render('bike_ride/list.html.twig', $response['parameters']);
    }
}
