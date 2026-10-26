<?php

declare(strict_types=1);

namespace App\Mapper\Level;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\LevelFilter;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\Level;
use App\Mapper\FilterChipsMapper;
use App\Mapper\Level\LevelMapper;
use App\Mapper\PaginatorMapper;
use App\Service\SeasonService;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LevelListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
        private LevelMapper $levelMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        LevelFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $route = $context->route;
        $currentPage = $context->page;
        $currentSeason = $this->seasonService->getCurrentSeason();
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];

        /** @var Level $entity */
        foreach ($entities as $entity) {
            $items[] = $this->levelMapper->mapToView($entity, $targetUrl, $currentSeason);
        }

        return new ListView(
            name: 'level',
            title: 'Niveaux',
            description: 'Administration des niveaux des adhérents du club.',
            items: $items,
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_level_list'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getAdvancedFields(), $filterConfig->getRouteName()),
            addItem: new LinkView(
                label: 'Ajouter un niveau',
                url: $this->urlContextService->generateUrl('admin_level_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
            sortableUrl: $this->urlContextService->generateUrl('admin_level_order', ['level' => 0], $targetUrl),
        );
    }
}
