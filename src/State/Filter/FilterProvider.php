<?php

declare(strict_types=1);

namespace App\State\Filter;

use App\Dto\SheetDto;
use App\Mapper\FilterMapper;
use App\Service\Filter\FilterConfigInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class FilterProvider
{
    public function __construct(
        #[TaggedIterator('app.filter_config')]
        private iterable $filterConfigs,
        private FilterMapper $filterMapper,
    ) {
    }

    public function getFilterConfig(string $route): ?FilterConfigInterface
    {
        foreach ($this->filterConfigs as $config) {
            if ($config->supports($route)) {
                return $config;
            }
        }

        return null;
    }

    public function getHydratedDto(array $rawData, string $dataClass): ?object
    {
        return $this->filterMapper->mapToDto($rawData, $dataClass);
    }

    public function createSheet(): SheetDto
    {
        return new SheetDto(
            title: 'Tous les filtres',
            description: 'Affiner votre récherche, trier les résutats',
            action: 'Rechercher',
        );
    }
}
