<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Form\BikeRideFilterType;
use App\Repository\BikeRideRepository;
use App\Repository\ContentRepository;
use App\Service\PaginatorService;
use App\UseCase\BikeRide\GetFilters;
use App\ViewModel\BikeRidesPresenter;
use App\ViewModel\Paginator\PaginatorPresenter;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RequestStack;

class GetSchedule
{
    public function __construct(
        private PaginatorService $paginator,
        private PaginatorPresenter $paginatorPresenter,
        private BikeRidesPresenter $bikeRidesPresenter,
        private BikeRideRepository $bikeRideRepository,
        private ContentRepository $contentRepository,
        private GetFilters $getFilters,
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function execute($request, $period, $year, $month, $day): array
    {
        $route = $request->get('_route');
        $filters = $this->getFiltersByParam($period, $year, $month, $day, $route);
        $form = $this->formFactory->create(BikeRideFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var ?SubmitButton  $next */
            $next = ($form->has('next') && $form->get('next') instanceof ClickableInterface) ? $form->get('next') : null;
            /** @var ?SubmitButton  $prev */
            $prev = ($form->has('prev') && $form->get('prev') instanceof ClickableInterface) ? $form->get('prev') : null;
            if ($next && $next->isClicked()) {
                $data['direction'] = BikeRide::DIRECTION_NEXT;
            }
            if ($prev && $prev->isClicked()) {
                $data['direction'] = BikeRide::DIRECTION_PREV;
            }
            // if ($form->has('today') && $form->get('today')->isClicked()) {
            //     $today = new DateTime();
            //     $data['date'] = $today->format('Y-m-d');
            // }
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
            $this->paginatorPresenter->present($bikeRides, $filters);
            $parameters['paginator'] = $this->paginatorPresenter->viewModel();
        } else {
            $bikeRides = $this->bikeRideRepository->findAllFiltered($filters);
            $parameters['paginator'] = null;
        }

        $this->bikeRidesPresenter->present($bikeRides);
        

        $parameters += [
            'form' => $form->createView(),
            'bikeRides' => $this->bikeRidesPresenter->viewModel()->bikeRides,
            'backgrounds' => $this->contentRepository->findOneByRoute('schedule')?->getBackgrounds(),
            'current_filters' => $filters,
        ];

        return [
            'parameters' => $parameters,
        ];
    }

    private function getFiltersByParam(?string $period, ?int $year, ?int $month, ?int $day, string $route)
    {
        $date = (null === $year && null === $month && null === $day) ? new DateTimeImmutable() : DateTimeImmutable::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}");
        if (null === $period) {
            $period = ('admin_events' === $route) ? BikeRide::PERIOD_WEEK : BikeRide::PERIOD_NEXT;
        }

        return $this->getFilters->execute($period, $date);
    }

    private function getFiltersByData(array $data)
    {
        $period = $data['period'];
        $date = new DateTimeImmutable($data['date']);
        $direction = (array_key_exists('direction', $data)) ? $data['direction'] : null;

        return $this->getFilters->execute($period, $date, $direction);
    }
}