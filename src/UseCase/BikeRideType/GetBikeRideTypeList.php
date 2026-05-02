<?php

declare(strict_types=1);

namespace App\UseCase\BikeRideType;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\DtoTransformer\BikeRideTypeDtoTransformer;
use App\Dto\Enum\ColorVariant;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Mapper\DropdownSettingsMapper;
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
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function execute(Request $request): array
    {
        $query = $this->bikeRideTypeRepository->findBikeRideTypeQuery();
        $bikeRideTypes = $this->paginator->paginate($query, $request->query->getInt('page', 1), PaginatorService::PAGINATOR_PER_PAGE);
        return [
            'bikeRideTypes' => $this->bikeRideTypeDtoTransformer->fromEntities($bikeRideTypes),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($bikeRideTypes),
            'settings' => $this->settings(),
        ];
    }

    private function settings(): DropdownDto
    {
        return $this->dropdownSettingsMapper->mapToView('BIKE_RIDE_TYPE', [
            new ButtonDto(
                label: 'Ajouter un message',
                url: $this->urlGenerator->generate('admin_message_add', ['sectionName' => 'BIKE_RIDE_TYPE']),
                icon: 'lucide:message-circle-plus',
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }
}
