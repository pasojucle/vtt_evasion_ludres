<?php

declare(strict_types=1);

namespace App\Mapper\Level;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\LevelFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\Level;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Repository\MemberRepository;
use App\Service\Filter\FilterConfigInterface;
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
        private MemberRepository $memberRepository,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        LevelFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];

        /** @var Level $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getTitle()),
                ],
                indicators: $this->getIndicators($entity),
                counter: $this->counter($entity, $currentSeason),
                dropdown: $this->dropDown($entity, $referer),
                gridTemplateBadges: 'grid-cols-[1fr_50px]'
            );
        }

        return new ListView(
            name: 'level',
            title: 'Niveaux',
            description: 'Administration des niveaux des adhérents du club.',
            items: $items,
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_level_list'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            addItem: new ButtonView(
                label: 'Ajouter un niveau',
                url: $this->urlGenerator->generate('admin_level_edit'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }

    private function getIndicators(Level $entity): array
    {
        return [
            new BadgeView(
                value: 'lucide:palette',
                size: Size::ICON,
                color: $entity->getColor(),
            ),
            new BadgeView(
                value: $entity->getType()->getIcon(),
                size: Size::ICON,
                variant: ColorVariant::ACCENT,
            ),
        ];
    }

    private function counter(Level $entity, int $currentSeason): BadgeView
    {
        return new BadgeView(
            value: (string) $this->memberRepository->countByLevelAndSeason($entity, $currentSeason),
        );
    }

    private function dropDown(Level $entity, ?string $referer): DropdownView
    {
        $menusItems = [];
        $menusItems[] = new ButtonView(
            label: 'Modifier',
            url: $this->urlContextService->generateUrl('admin_level_edit', ['level' => $entity->getId()], $referer),
            icon: 'lucide:pencil',
            variant: ColorVariant::DROPDOWN,
        );
        if (!$entity->isProtected()) {
            $menusItems[] = new ButtonView(
                label: 'Supprimer',
                url: $this->urlContextService->generateUrl('admin_level_delete', ['level' => $entity->getId()], $referer),
                icon: 'lucide:delete',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            );
        }
        return  new DropdownView(
            menuItems: $menusItems
        );
    }
}
