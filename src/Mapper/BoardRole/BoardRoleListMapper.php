<?php

declare(strict_types=1);

namespace App\Mapper\BoardRole;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\BoardRoleFilter;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\BoardRole;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BoardRoleListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private BoardRoleMapper $boardRoleMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        BoardRoleFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'board_role',
            title: 'Rôles du bureau et comité',
            description: 'Administration des rôles du bureau et comité.',
            items: array_map(fn(BoardRole $entity) => $this->boardRoleMapper->mapToView($entity, $targetUrl), iterator_to_array($entities)),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getAdvancedFields(), $filterConfig->getRouteName()),
            addItem: new LinkView(
                label: 'Ajouter un rôle',
                url: $this->urlContextService->generateUrl('admin_board_role_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
            fallback: new LinkView(
                url: $this->urlContextService->decodeUrl($context->encodedFallback),
                icon: 'lucide:chevron-left',
                rounded: RoundedVariant::ROUNDED,
                size: Size::ICON,
            ),
            sortableUrl: $this->urlContextService->generateUrl('admin_board_role_order', ['boardRole' => 0], $targetUrl),
        );
    }
}
