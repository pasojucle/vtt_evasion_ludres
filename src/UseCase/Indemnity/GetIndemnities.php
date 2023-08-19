<?php

declare(strict_types=1);

namespace App\UseCase\Indemnity;

use App\Dto\DtoTransformer\IndemnityDtoTransformer;
use App\Repository\BikeRideTypeRepository;
use App\Repository\IndemnityRepository;
use App\Repository\LevelRepository;

class GetIndemnities
{
    public function __construct(
        private LevelRepository $levelRepository,
        private IndemnityRepository $indemnityRepository,
        private BikeRideTypeRepository $bikeRideTypeRepository,
        private IndemnityDtoTransformer $indemnityDtoTransformer
    ) {
    }

    public function execute(): array
    {
        $frameLevels = $this->levelRepository->findAllTypeFramer();
        $indemnities = $this->indemnityDtoTransformer->fromEntities($this->indemnityRepository->findOrderByBikeRideType());
        $bikeRidesTypes = $this->bikeRideTypeRepository->findCompensables();

        $values = [];
        if (!empty($frameLevels) && !empty($bikeRidesTypes)) {
            foreach ($frameLevels as $frameLevel) {
                foreach ($bikeRidesTypes as $bikeRidesType) {
                    $indemnity = (array_key_exists($frameLevel->getId(), $indemnities) && array_key_exists($bikeRidesType->getId(), $indemnities[$frameLevel->getId()]))
                        ? $indemnities[$frameLevel->getId()][$bikeRidesType->getId()]
                        : null;
                    $values[$frameLevel->getId()]['name'] = $frameLevel->getTitle();
                    $values[$frameLevel->getId()]['bikeRideTypes'][$bikeRidesType->getId()] = ['name' => $bikeRidesType->getName(), 'indemnity' => $indemnity];
                }
            }
        }

        return ['header' => $bikeRidesTypes, 'values' => $values];
    }
}
