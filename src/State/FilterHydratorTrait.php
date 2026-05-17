<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\Filter\AbstractFilter;
use App\Mapper\FilterMapper;
use App\Service\Filter\FilterConfigInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Contracts\Service\Attribute\Required;

trait FilterHydratorTrait
{
    private FilterMapper $filterMapper;
    private iterable $filterConfigs;

    #[Required()]
    public function setFilterServices(
        FilterMapper $filterMapper,
        #[TaggedIterator('app.filter_config')]
        iterable $filterConfigs
    ): void {
        $this->filterMapper = $filterMapper;
        $this->filterConfigs = $filterConfigs;
    }

    public function getHydratedDto(array $rawData, string $dataClass): ?AbstractFilter
    {
        return $this->filterMapper->mapToDto($rawData, $dataClass);
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
}