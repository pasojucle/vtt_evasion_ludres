<?php

declare(strict_types=1);

namespace App\Mapper\Skill;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SkillFilter;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\Skill;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SkillListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SkillMapper $skillMapper,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        SkillFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'skill',
            title: 'Compétences',
            description: 'Administration de la liste des compétences.',
            items: array_map(fn (Skill $skill) => $this->skillMapper->mapToView($skill, $targetUrl), iterator_to_array($entities)),
            tools: $this->getTools($filter->toArray()),
            settings: $this->settings($targetUrl),
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
                label: 'Ajouter une compétence',
                url: $this->urlContextService->generateUrl('admin_skill_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
        );
    }

    private function settings(string $targetUrl): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView(null, $targetUrl, RoundedVariant::ROUNDED, [
            new LinkView(
                label: 'Catégories',
                url: $this->urlGenerator->generate('admin_skill_category_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function getTools(array $filter): DropdownView
    {
        $dropdown = new DropdownView(
            variant: DropdownVariant::GOST,
            menuItems: [
                new LinkView(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_skill_export', $filter),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                        new HtmlAttributeView('data-turbo', 'false')
                    ]
                ),
            ],
        );

        return $dropdown;
    }
}
