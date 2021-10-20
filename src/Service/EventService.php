<?php

namespace App\Service;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Entity\Event;
use App\Entity\Level;
use App\Entity\Cluster;
use App\Form\EventFilterType;
use App\Service\PaginatorService;
use App\Repository\EventRepository;
use App\Repository\LevelRepository;
use App\Repository\ParameterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class EventService
{
    private PaginatorService $paginator;
    private RequestStack $requestStack;
    private FormFactoryInterface $formFactory;
    private EventRepository $eventRepository;
    private LevelRepository $levelRepository;
    private EntityManagerInterface $entityManager;
    private ParameterRepository $parameterRepository;

    public function __construct(
        PaginatorService $paginator,
        RequestStack $requestStack, 
        FormFactoryInterface $formFactory, 
        EventRepository $eventRepository,
        LevelRepository $levelRepository,
        EntityManagerInterface $entityManager,
        ParameterRepository $parameterRepository
    )
    {
        $this->paginator = $paginator;
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
        $this->eventRepository = $eventRepository;
        $this->levelRepository = $levelRepository;
        $this->entityManager = $entityManager;
        $this->parameterRepository = $parameterRepository;
    }
    
    
    public function getFiltersByParam(?string $period, ?int $year, ?int $month, ?int $day, string $route) {
        $date = (null === $year && null === $month && null === $day) ? new DateTime(): DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        if (null === $period) {
            $period = ('admin_events' === $route) ? Event::PERIOD_WEEK : Event::PERIOD_NEXT;
        }

        return $this->getFilters($period, $date);
    }

    public function getFiltersByData(array $data) {
        $period = $data['period'];
        $date = new DateTime($data['date']);
        $direction = (array_key_exists('direction', $data)) ? $data['direction'] : null;

        return $this->getFilters($period, $date, $direction);
    }

    public function getFilters(string $period, DateTime $date, ?int $direction = null) {
        if (null !== $direction && !in_array($period, [Event::PERIOD_ALL, Event::PERIOD_NEXT])) {
            $intervals = [
                Event::PERIOD_DAY => "P1D",
                Event::PERIOD_WEEK => "P1W",
                Event::PERIOD_MONTH => "P1M1D",
            ];
            if (Event::DIRECTION_PREV === $direction) {
                $date->sub(new DateInterval($intervals[$period]));
            }
            if (Event::DIRECTION_NEXT === $direction) {
                $date->add(new DateInterval($intervals[$period]));
            }
        }
        $startAt = clone $date;
        $endAt = clone $date;
        $limit = null;
        switch ($period) {
            case Event::PERIOD_DAY:
                $startAt = $startAt;
                $endAt = $endAt;
                break;
            
            case Event::PERIOD_WEEK:
                $startAt =  $startAt->modify('monday this week');
                $endAt = $endAt->modify('sunday this week');
                break;
            
            case Event::PERIOD_MONTH:
                $startAt =  $startAt->modify('first day of this month');
                $endAt = $endAt->modify('last day of this month');
                break;
            
            case Event::PERIOD_NEXT:
                $startAt = $startAt;
                $endAt = null;
                $limit = 6;
                break;
            default:
                $startAt = null;
                $endAt = null;
        }
        if (null !== $startAt) {
            $startAt =  DateTime::createFromFormat('Y-m-d H:i:s', $startAt->format('Y-m-d').' 00:00:00');
        }
        if (null !== $endAt) {
            $endAt =  DateTime::createFromFormat('Y-m-d H:i:s', $endAt->format('Y-m-d').' 23:59:59');
        }

        return ['startAt' => $startAt,
            'endAt' => $endAt, 
            'period' => $period, 
            'year' => $date->format('Y'), 
            'month' => $date->format('m'), 
            'day' => $date->format('d'),
            'date' => $date->format('y-m-d'),
            'limit' => $limit,
        ];
    }

    public function getSchedule($request, $period, $year, $month, $day): array
    {
        $route = $request->get('_route');
        $filters = $this->getFiltersByParam($period, $year, $month, $day, $route);
        $form = $this->formFactory->create(EventFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($form->has('next') && $form->get('next')->isClicked()) {
                $data['direction'] = Event::DIRECTION_NEXT;
            }
            if ($form->has('prev') && $form->get('prev')->isClicked()) {
                $data['direction'] = Event::DIRECTION_PREV;
            }
            if ($form->has('today') && $form->get('today')->isClicked()) {
                $today = new DateTime();
                $data['date'] = $today->format('Y-m-d');
            }

            $filters = $this->getFiltersByData($data);
            $this->requestStack->getSession()->set('admin_events_filters', $filters);
            return ['redirect' => $route, 'filters' => $filters];
        }
        $parameters = [];
        if (null == $filters['limit']) {
            $query = $this->eventRepository->findAllQuery($filters);
            $events =  $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
            $parameters['lastPage'] = $this->paginator->lastPage($events);
        } else {
            $events =  $this->eventRepository->findAllFiltered($filters);
            $parameters['lastPage'] = null;
        }
        
        $parameters += [
            'form' => $form->createView(),
            'events' => $events,
            'current_filters' => $filters,
        ];

        return ['parameters' => $parameters];
    }

    public function createClusters($event)
    {
        switch ($event->getType()) {
            case Event::TYPE_SCHOOL:
                $cluster = new Cluster();
                $cluster->setTitle(Cluster::CLUSTER_FRAME)
                    ->setRole('ROLE_FRAME');
                $event->addCluster($cluster);
                $this->entityManager->persist($cluster);
                $levels = $this->levelRepository->findAllTypeMember();
                if (null !== $levels) {
                    foreach ($levels as $level) {
                        $cluster = new Cluster();
                        $cluster->setTitle($level->getTitle())
                            ->setLevel($level)
                            ->setMaxUsers(Cluster::SCHOOL_MAX_MEMEBERS);
                        $event->addCluster($cluster);
                        $this->entityManager->persist($cluster);
                    }
                }
                break;
            default:
                $cluster = new Cluster();
                $cluster->setTitle('1er Groupe');
                $event->addCluster($cluster);
                $this->entityManager->persist($cluster);
        }
            
            // $this->entityManager->flush();
    }

    public function setDefaultContent(Request $request, Event $event): Event
    {
        $eventRequest = $request->request->get('event');
        if (null !== $eventRequest) {
            $type = (int) $eventRequest['type'];
            $parameterName = null;
            if (Event::TYPE_SCHOOL === $type) {
                $parameterName = 'EVENT_SCHOOL_CONTENT';
            }
            if (Event::TYPE_ADULT === $type) {
                $parameterName = 'EVENT_ADULT_CONTENT';
            }
            if (null !== $parameterName) {
                $parameter = $this->parameterRepository->findOneByName($parameterName);
                if (null !== $parameter) {
                    $event->setTitle($parameter->getLabel())->setContent($parameter->getValue());
                }
            }
        }

        return $event;
    }

    public function getEventWithPresentsByCluster(Event $event): array
    {
        $clusters = [];
        if (!$event->getClusters()->isEmpty()) {
            foreach ($event->getClusters() as $cluster) {
                $clusters[] = [
                    'cluster' => $cluster,
                    'presentCount' => $this->getCountOfPresents($cluster->getSessions()),
                ];
            }
        }

        return [
            'title' => $event->getTitle(),
            'startAt' => $event->getStartAt(),
            'endAt' => $event->getEndAt(),
            'type' => $event->getType(),
            'id' => $event->getId(),
            'clusters' => $clusters,
        ];
    }

    public function getCountOfPresents(Collection $sessions): int
    {
        $presentSessions = [];
        if (!$sessions->isEmpty()) {
            foreach ($sessions as $session) {
                $level = $session->getUser()->getLevel();
                $levelType = (null !== $level) ? $level->getType() : Level::TYPE_MEMBER;
                if ($session->isPresent() && $levelType === Level::TYPE_MEMBER ) {
                    $presentSessions[] = $session;
                }
            }
        }
        return count($presentSessions);
    }
}