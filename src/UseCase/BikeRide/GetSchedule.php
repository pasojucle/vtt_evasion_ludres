<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Entity\BikeRide;
use App\Form\BikeRideFilterType;
use App\Repository\BikeRideRepository;
use App\Repository\ContentRepository;
use App\Repository\ParameterRepository;
use App\Service\PaginatorService;
use App\UseCase\BikeRide\GetFilters;
use DateTimeImmutable;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use ValueError;

class GetSchedule
{
    public function __construct(
        private PaginatorService $paginator,
        private PaginatorDtoTransformer $paginatorDtoTransformer,
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private BikeRideRepository $bikeRideRepository,
        private ContentRepository $contentRepository,
        private ParameterRepository $parameterRepository,
        private GetFilters $getFilters,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function execute(Request $request, ?string $period, ?int $year, ?int $month, ?int $day): array
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
            $request->getSession()->set('admin_bike_rides_filters', $filters);

            return [
                'redirect' => $route,
                'filters' => $filters,
            ];
        }
        $parameters = [];
        if (null === $filters['limit']) {
            $query = $this->bikeRideRepository->findAllQuery($filters);
            $bikeRides = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
            $parameters['paginator'] = $this->paginatorDtoTransformer->fromEntities($bikeRides, $filters);
        } else {
            $bikeRides = $this->bikeRideRepository->findAllFiltered($filters);
            $parameters['paginator'] = null;
        }

        $parameters += [
            'form' => $form->createView(),
            'bikeRides' => $this->bikeRideDtoTransformer->fromEntities($bikeRides),
            'backgrounds' => $this->contentRepository->findOneByRoute('schedule')?->getBackgrounds(),
            'current_filters' => $filters,
            'settings' => [
                'parameters' => $this->parameterRepository->findByParameterGroupName('BIKE_RIDE'),
                'routes' => [
                    ['name' => 'admin_bike_ride_types', 'label' => 'Types de rando'],
                    ['name' => 'admin_indemnity_list', 'label' => 'Indemnités'],
                ],
            ],
        ];

        return [
            'parameters' => $parameters,
        ];
    }

    private function getFiltersByParam(?string $period, ?int $year, ?int $month, ?int $day, string $route)
    {
        $date = new DateTimeImmutable();
        if ($year && $month && $day && $dateFromYearMontDay = DateTimeImmutable::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}")) {
            $date = $dateFromYearMontDay;
        }
        
        if (null === $period) {
            $period = ('admin_bike_rides' === $route) ? BikeRide::PERIOD_NEXT : BikeRide::PERIOD_MONTH;
        }

        return $this->getFilters->execute($period, $date);
    }

    private function getFiltersByData(array $data)
    {
        $period = $data['period'];
        try {
            $date = DateTimeImmutable::createFromFormat('y-m-d', $data['date']);
        } catch (ValueError) {
            $date = new DateTimeImmutable();
        }
        
        $direction = (array_key_exists('direction', $data)) ? $data['direction'] : null;

        return $this->getFilters->execute($period, $date, $direction);
    }
}
