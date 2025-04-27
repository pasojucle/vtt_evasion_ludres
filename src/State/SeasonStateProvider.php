<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\ChoiceDto;
use App\Dto\DtoTransformer\ChoiceDtoTransformer;
use App\Service\SeasonService;
use Doctrine\Common\Collections\ArrayCollection;

class SeasonStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly SeasonService $seasonService,
        private readonly ChoiceDtoTransformer $transformer,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $seasons = [];
        foreach (range(2021, $this->seasonService->getCurrentSeason()) as $season) {
            $seasons[] = $this->transformer->fromValue($season, 'Saison')->toArray();
        }

        return new ArrayCollection(array_reverse($seasons));
    }
}
