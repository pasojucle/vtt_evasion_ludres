<?php

declare(strict_types=1);

namespace App\UseCase\BikeRideType;

use App\Dto\DropdownDto;
use App\Dto\DtoTransformer\BikeRideTypeDtoTransformer;
use App\Dto\DtoTransformer\DropdownDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\RouteDto;
use App\Repository\BikeRideTypeRepository;
use App\Service\PaginatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetBikeRideTypeList
{
    public function __construct(
        private BikeRideTypeRepository $bikeRideTypeRepository,
        private BikeRideTypeDtoTransformer $bikeRideTypeDtoTransformer,
        private PaginatorService $paginator,
        private PaginatorDtoTransformer $paginatorDtoTransformer,
        private DropdownDtoTransformer $dropdownDtoTransformer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function execute(Request $request): array
    {
        $query = $this->bikeRideTypeRepository->findBikeRideTypeQuery();
        $bikeRideTypes = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        return [
            'bikeRideTypes' => $this->bikeRideTypeDtoTransformer->fromEntities($bikeRideTypes),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($bikeRideTypes),
            'settings' => $this->settings(),
        ];
    }

    private function settings(): DropdownDto
    {
        $dropdown = $this->dropdownDtoTransformer->fromSettings('BIKE_RIDE_TYPE');
        $dropdown->setUrlGenerator($this->urlGenerator);
        $dropdown->addMenuItem(
            'Ajouter un message',
            new RouteDto('admin_message_add', ['sectionName' => 'BIKE_RIDE_TYPE']),
            'lucide:message-circle-plus'
        );
        return $dropdown;
    }
}
