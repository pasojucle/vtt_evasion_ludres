<?php

namespace App\Service;

use DateTime;
use DateInterval;
use App\Entity\Event;
use App\Form\EventFilterType;
use App\Repository\EventRepository;
use App\Service\PaginatorService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EventService
{
    private PaginatorService $paginator;
    private SessionInterface $session;
    private FormFactoryInterface $formFactory;
    private EventRepository $eventRepository;

    public function __construct(PaginatorService $paginator, SessionInterface $session, FormFactoryInterface $formFactory, EventRepository $eventRepository)
    {
        $this->paginator = $paginator;
        $this->session = $session;
        $this->formFactory = $formFactory;
        $this->eventRepository = $eventRepository;
    }
    
    
    public function getFiltersByParam(?string $period, ?int $year, ?int $month, ?int $day, string $route) {
        $date = (null === $year && null === $month && null === $day) ? new DateTime(): DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        if (null === $period) {
            $period = ('admin_events' === $route) ? Event::PERIOD_WEEK : Event::PERIOD_MONTH;
        }

        return $this->getFilters($period, $date);
    }

    public function getFiltersByData(array $data) {
        $period = $data['period'];
        $date = new DateTime($data['date']);
        $direction = (array_key_exists('direction', $data)) ? $data['direction'] : null;

        return $this->getFilters($period, $date, $direction);
    }

    private function getFilters(string $period, DateTime $date, ?int $direction = null) {
        if (null !== $direction && Event::PERIOD_ALL !== $period) {
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
        $stardAt = clone $date;
        $endAt = clone $date;
        switch ($period) {
            case Event::PERIOD_DAY:
                $stardAt = $stardAt;
                $endAt = $endAt;
                break;
            
            case Event::PERIOD_WEEK:
                $stardAt =  $stardAt->modify('monday this week');
                $endAt = $endAt->modify('sunday this week');
                break;
            
            case Event::PERIOD_MONTH:
                $stardAt =  $stardAt->modify('first day of this month');
                $endAt = $endAt->modify('last day of this month');
                break;
            
            default:
                $stardAt = null;
                $endAt = null;
        }
        if (null !== $stardAt && null !== $endAt) {
            $stardAt =  DateTime::createFromFormat('Y-m-d H:i:s', $stardAt->format('Y-m-d').' 00:00:00');
            $endAt =  DateTime::createFromFormat('Y-m-d H:i:s', $endAt->format('Y-m-d').' 23:59:59');
        }

        return ['startAt' => $stardAt,
            'endAt' => $endAt, 
            'period' => $period, 
            'year' => $date->format('Y'), 
            'month' => $date->format('m'), 
            'day' => $date->format('d'),
            'date' => $date->format('y-m-d'),
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
            $this->session->set('admin_events_filters', $filters);
            return ['redirect' => $route, 'filters' => $filters];
        }

        $query =  $this->eventRepository->findAllQuery($filters);
        $bikeRides =  $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $parameters = [
            'form' => $form->createView(),
            'bikeRides' => $bikeRides,
            'lastPage' => $this->paginator->lastPage($bikeRides),
            'filters' => $filters,
        ];

        return ['parameters' => $parameters];
    }
}