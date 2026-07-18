<?php

declare(strict_types=1);

namespace App\Mapper\Level;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\LevelFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\LinkView;
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
                status: $this->getStatus($entity),
                counter: $this->counter($entity, $currentSeason),
                isDeleted: $entity->isDeleted(),
                dropdown: $this->dropDown($entity, $referer),
                gridTemplateBadges: 'grid-cols-[1fr_70px]'
            );
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
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            addItem: new LinkView(
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

    private function counter(Level $entity, int $currentSeason): ?BadgeView
    {
        if ($entity->isDeleted()) {
            return null;
        }
        
        return new BadgeView(
            value: (string) $this->memberRepository->countByLevelAndSeason($entity, $currentSeason),
        );
    }

    private function dropDown(Level $entity, ?string $referer): DropdownView
    {
        if ($entity->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_level_restore', [
                            'level' => $entity->getId()
                            ], $referer),
                        icon: 'lucide:archive-restore',
                        variant: ColorVariant::DROPDOWN,
                    ),
                ]
            );
        }

        $menusItems = [];
        $menusItems[] = new LinkView(
            label: 'Modifier',
            url: $this->urlContextService->generateUrl('admin_level_edit', ['level' => $entity->getId()], $referer),
            icon: 'lucide:pencil',
            variant: ColorVariant::DROPDOWN,
        );
        if (!$entity->isProtected()) {
            $menusItems[] = new LinkView(
                label: 'Supprimer',
                url: $this->urlContextService->generateUrl('admin_level_delete', ['level' => $entity->getId()], $referer),
                icon: 'lucide:delete',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            );
        }
        return  new DropdownView(
            menuItems: $menusItems
        );
    }

    private function getStatus(Level $entity): ?BadgeView
    {
        if ($entity->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }
}
