<?php

declare(strict_types=1);

namespace App\Mapper\BoardRole;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\BoardRoleFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\LinkView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\BoardRole;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BoardRoleListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        BoardRoleFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];

        /** @var BoardRole $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getName()),
                ],
                status: $this->getStatus($entity),
                isDeleted: $entity->isDeleted(),
                dropdown: $this->dropDown($entity, $referer),
                gridTemplateContent: 'grid-cols-[1fr_80px]'
            );
        }

        return new ListView(
            name: 'board_role',
            title: 'Rôles du bureau et comité',
            description: 'Administration des rôles du bureau et comité.',
            items: $items,
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            addItem: new LinkView(
                label: 'Ajouter un rôle',
                url: $this->urlGenerator->generate('admin_bike_ride_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }
    private function dropDown(BoardRole $entity, string $referer): DropdownView
    {
        if ($entity->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_board_role_restore', [
                            'boardRole' => $entity->getId()
                            ], $referer),
                        icon: 'lucide:archive-restore',
                        variant: ColorVariant::DROPDOWN,
                    ),
                ]
            );
        }
        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_board_role_edit', ['boardRole' => $entity->getId()], $referer),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
                 new LinkView(
                     label: 'Supprimer',
                     url: $this->urlContextService->generateUrl('admin_board_role_delete', ['boardRole' => $entity->getId()], $referer),
                     icon: 'lucide:delete',
                     variant: ColorVariant::DROPDOWN,
                     htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                 )
            ]
        );
    }

    private function getStatus(BoardRole $entity): ?BadgeView
    {
        if ($entity->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }
}
