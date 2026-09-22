<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\Size;
use App\Dto\View\LinkView;
use App\Dto\View\TabView;
use App\Dto\View\TabWrapperView;
use App\Entity\BikeRide;
use App\Mapper\Cluster\ClustersActivityMapper;
use App\Mapper\Cluster\ClusterTabMapper;
use App\Service\UrlContextService;

/**
 * @implements ComponentProviderInterface<BikeRide>
 */
class ClustersActivityReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private ClustersActivityMapper $clutersActivityMapper,
        private ClusterTabMapper $clusterMapper,
        private UrlContextService $urlContext,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): TabWrapperView
    {
        $fallback = $context->encodedFallback;
        $currentTab = $context->tab;

        return new TabWrapperView(
            name: sprintf('bike-ride-%s', $data->getId()),
            title: $data->getTitle(),
            header: $this->clutersActivityMapper->mapToView($data),
            tabs: $this->tabs($data, $fallback, $currentTab),
            fallback: new LinkView(
                url: $this->urlContext->decodeUrl($context->encodedFallback),
                icon: 'lucide:chevron-left',
                size: Size::ICON
            ),
        );
    }

    private function tabs(BikeRide $data, ?string $fallback, int $currentTab): array
    {
        $tabs = [];
        $index = 0;

        foreach ($data->getClusters() as $cluster) {
            $level = $cluster->getLevel();

            $tabs[] = new TabView(
                title: $cluster->getTitle(),
                view: $this->clusterMapper->mapToView($cluster, $fallback),
                color: $level?->getColor(),
                index: $index++,
                isActive: $index === $currentTab,
            );
        }

        return $tabs;
    }
}
