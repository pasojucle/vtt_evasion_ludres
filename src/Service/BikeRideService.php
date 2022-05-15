<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Form\BikeRideFilterType;
use App\Repository\BikeRideRepository;
use App\Repository\BikeRideTypeRepository;
use App\Repository\LevelRepository;
use App\Repository\ParameterRepository;
use App\ViewModel\BikeRidesPresenter;
use App\ViewModel\ClusterPresenter;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class BikeRideService
{
    public function __construct(
        private PaginatorService $paginator,
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private BikeRideRepository $bikeRideRepository,
        private LevelRepository $levelRepository,
        private EntityManagerInterface $entityManager,
        private ParameterRepository $parameterRepository,
        private BikeRidesPresenter $bikeRidesPresenter,
        private ClusterPresenter $clusterPresenter,
        private BikeRideTypeRepository $bikeRideTypeRepository
    ) {
    }

    public function getFiltersByParam(?string $period, ?int $year, ?int $month, ?int $day, string $route)
    {
        $date = (null === $year && null === $month && null === $day) ? new DateTimeImmutable() : DateTimeImmutable::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}");
        if (null === $period) {
            $period = ('admin_events' === $route) ? BikeRide::PERIOD_WEEK : BikeRide::PERIOD_NEXT;
        }

        return $this->getFilters($period, $date);
    }

    public function getFiltersByData(array $data)
    {
        $period = $data['period'];
        $date = new DateTimeImmutable($data['date']);
        $direction = (array_key_exists('direction', $data)) ? $data['direction'] : null;

        return $this->getFilters($period, $date, $direction);
    }

    public function getFilters(string $period, DateTimeImmutable $date, ?int $direction = null)
    {
        if (null !== $direction && !in_array($period, [BikeRide::PERIOD_ALL, BikeRide::PERIOD_NEXT], true)) {
            $intervals = [
                BikeRide::PERIOD_DAY => 'P1D',
                BikeRide::PERIOD_WEEK => 'P1W',
                BikeRide::PERIOD_MONTH => 'P1M1D',
            ];

            if (BikeRide::DIRECTION_PREV === $direction) {
                $date = $date->sub(new DateInterval($intervals[$period]));
            }
            if (BikeRide::DIRECTION_NEXT === $direction) {
                $date = $date->add(new DateInterval($intervals[$period]));
            }
        }
        $startAt = clone $date;
        $endAt = clone $date;
        $limit = null;
        switch ($period) {
            case BikeRide::PERIOD_DAY:
                $startAt = $startAt;
                $endAt = $endAt;

                break;
            case BikeRide::PERIOD_WEEK:
                $startAt = $startAt->modify('monday this week');
                $endAt = $endAt->modify('sunday this week');

                break;
            case BikeRide::PERIOD_MONTH:
                $startAt = $startAt->modify('first day of this month');
                $endAt = $endAt->modify('last day of this month');

                break;
            case BikeRide::PERIOD_NEXT:
                $startAt = $startAt;
                $endAt = null;
                $limit = 6;

                break;
            default:
                $startAt = null;
                $endAt = null;
        }
        if (null !== $startAt) {
            $startAt = DateTime::createFromFormat('Y-m-d H:i:s', $startAt->format('Y-m-d').' 00:00:00');
        }
        if (null !== $endAt) {
            $endAt = DateTime::createFromFormat('Y-m-d H:i:s', $endAt->format('Y-m-d').' 23:59:59');
        }

        return [
            'startAt' => $startAt,
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
        $form = $this->formFactory->create(BikeRideFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($form->has('next') && $form->get('next')->isClicked()) {
                $data['direction'] = BikeRide::DIRECTION_NEXT;
            }
            if ($form->has('prev') && $form->get('prev')->isClicked()) {
                $data['direction'] = BikeRide::DIRECTION_PREV;
            }
            if ($form->has('today') && $form->get('today')->isClicked()) {
                $today = new DateTime();
                $data['date'] = $today->format('Y-m-d');
            }
            $filters = $this->getFiltersByData($data);
            $this->requestStack->getSession()->set('admin_bike_rides_filters', $filters);

            return [
                'redirect' => $route,
                'filters' => $filters,
            ];
        }
        $parameters = [];
        if (null === $filters['limit']) {
            $query = $this->bikeRideRepository->findAllQuery($filters);
            $bikeRides = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
            $parameters['lastPage'] = $this->paginator->lastPage($bikeRides);
        } else {
            $bikeRides = $this->bikeRideRepository->findAllFiltered($filters);
            $parameters['lastPage'] = null;
        }

        $this->bikeRidesPresenter->present($bikeRides);

        $parameters += [
            'form' => $form->createView(),
            'bikeRides' => $this->bikeRidesPresenter->viewModel()->bikeRides,
            'current_filters' => $filters,
        ];

        return [
            'parameters' => $parameters,
        ];
    }

    public function createClusters($bikeRide)
    {
        if ($bikeRide->getBikeRideType()->isRegistrable()) {
            if ($bikeRide->getBikeRideType()->isSchool()) {
                $cluster = new Cluster();
                $cluster->setTitle(Cluster::CLUSTER_FRAME)
                    ->setRole('ROLE_FRAME')
                ;
                $bikeRide->addCluster($cluster);
                $this->entityManager->persist($cluster);
                $levels = $this->levelRepository->findAllTypeMember();
                if (null !== $levels) {
                    foreach ($levels as $level) {
                        $cluster = new Cluster();
                        $cluster->setTitle($level->getTitle())
                            ->setLevel($level)
                            ->setMaxUsers(Cluster::SCHOOL_MAX_MEMEBERS)
                        ;
                        $bikeRide->addCluster($cluster);
                        $this->entityManager->persist($cluster);
                    }
                }
            } else {
                $cluster = new Cluster();
                $cluster->setTitle('1er Groupe');
                $bikeRide->addCluster($cluster);
                $this->entityManager->persist($cluster);
            }
        }
    }

    public function setDefaultContent(Request $request, BikeRide $bikeRide): BikeRide
    {
        $bikeRideRequest = $request->request->all('bike_ride');
        if (null !== $bikeRideRequest && array_key_exists('bikeRideType', $bikeRideRequest)) {
            $type = $this->bikeRideTypeRepository->find((int) $bikeRideRequest['bikeRideType']);
            $bikeRide->setBikeRideType($type);
            $bikeRide->setTitle($type->getName())
                ->setContent($type->getContent());
        }

        return $bikeRide;
    }
}
