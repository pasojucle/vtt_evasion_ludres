<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Dto\Enum\Size;
use App\Dto\View\LinkView;
use App\Dto\View\TabView;
use App\Dto\View\TabWrapperView;
use App\Entity\Cluster;
use App\Entity\BikeRide;
use App\Mapper\Cluster\ClusterTabMapper;
use App\Mapper\Cluster\ClustersActivityMapper;
use App\State\Interface\ComponentProviderInterface;

/**
 * @implements ComponentProviderInterface<BikeRide>
 */
class ClustersActivityReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private ClustersActivityMapper $clutersActivityMapper,
        private ClusterTabMapper $clusterMapper,
    ) {
    }

    public function getView(object $entity, ?string $fallback = null, ?string $referer = null): TabWrapperView
    {
        return new TabWrapperView(
            name: sprintf('bike-ride-%s', $entity->getId()),
            title: $entity->getTitle(),
            header: $this->clutersActivityMapper->mapToView($entity),
            tabs: $entity->getClusters()->map(function(Cluster $cluster) {
                $level = $cluster->getLevel();

                return new TabView(
                    title: $cluster->getTitle(),
                    view: $this->clusterMapper->mapToView($cluster),
                    color: $level?->getColor(),
                );
            })->toArray(),
            fallback: new LinkView(
                url: $fallback,
                icon: 'lucide:chevron-left',
                size: Size::ICON
            ),
        );
    }
}
