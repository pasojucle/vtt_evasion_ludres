<?php
declare(strict_types=1);

namespace App\Mapper\SecondHandCategory;

use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SecondHandCategoryFilter;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListView;
use App\Dto\View\ListItemView;
use App\Entity\SecondHandCategory;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecondHandCategoryListMapper
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
        SecondHandCategoryFilter $filter,
        FilterConfigInterface $filterConfig
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];
        /** @var SecondHandCategory $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getName()),
                ],                
                indicators: $this->getIndicators($entity),
                dropdown: $this->dropDown($entity, $referer),
                gridTemplateContent: 'grid-cols-[1fr_50px]',
            );
        }

        return new ListView(
            name: 'second_hand_category',
            title: 'Catégories',
            description: 'Administration des catégories des annonces d\'occasion.',
            items: $items,
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            addItem: new ButtonView(
                label: 'Ajouter une catégorie',
                url: $this->urlGenerator->generate('admin_second_hand_category_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }

    private function getIndicators(SecondHandCategory $entity): array
    {
        return [
            new BadgeView(
                value: $entity->getIcon(),
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            )
        ];
    }

    private function dropDown(SecondHandCategory $entity, string $referer): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new ButtonView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_second_hand_category_edit', ['category' => $entity->getId()], $referer),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                     htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                ),
                 new ButtonView(
                     label: 'Supprimer',
                     url: $this->urlContextService->generateUrl('admin_second_hand_category_delete', ['category' => $entity->getId()], $referer),
                     icon: 'lucide:delete',
                     variant: ColorVariant::DROPDOWN,
                     htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                 )
            ]
        );
    }
}
