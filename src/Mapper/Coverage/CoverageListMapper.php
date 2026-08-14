<?php

declare(strict_types=1);

namespace App\Mapper\Coverage;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\CoverageFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownItemView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Member;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\User\UserDropdownMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\SeasonService;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CoverageListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UserDropdownMapper $userDropdownMapper,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        CoverageFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];

        /** @var Member $entity */
        foreach ($entities as $entity) {
            $identity = $entity->getIdentity();
            $licence = $entity->getLastLicence();

            $items[] = new ListItemView(
                labels: [
                    new LabelView($identity->getFullName()),
                ],
                indicators: $this->getIndicators($entity->getLevel()),
                dropdown: $this->userDropdownMapper->mapToView($entity, $referer),
                url: $this->urlGenerator->generate("admin_user_show", ['user' => $entity->getId()]),
                action: $this->getAction($licence, $referer),
                gridTemplateRow: 'grid-cols-1 lg:grid-cols-[1fr_112px]',
                gridTemplateContent: 'grid-cols-1 lg:grid-cols-[1fr_200px_100px]',
            );
        }
        $currentSeason = $this->seasonService->getCurrentSeason();

        return new ListView(
            name: 'coverage',
            title: sprintf('Assurances %s', $currentSeason),
            description: sprintf('Administration des assurances ffvélo, pour les adhérents ayant soucrit avant la publication des bulletins d\'assurance %s .', $currentSeason),
            items: $items,
            tools: $this->tools($filter),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_coverage_list'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
        );
    }

    private function getIndicators(?Level $level): array
    {
        $indicators = [];
        if ($level) {
            $indicators[] = new BadgeView(
                value:$level->getTitle(),
                color: $level->getColor(),
            );
        }

        return $indicators;
    }

    public function tools(CoverageFilter $filter): ?DropdownView
    {
        return new DropdownView(
            variant: DropdownVariant::GOST,
            menuItems: [
                new LinkView(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_coverages_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                        new HtmlAttributView('data-turbo', 'false')
                    ],
                )
            ],
            actionItems: [
                new DropdownItemView(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributView('data-controller', 'email-to-clipboard'),
                        new HtmlAttributView('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                        new HtmlAttributView('data-email-to-clipboard-url-value', $this->urlGenerator->generate(
                            'admin_coverages_email_to_clipboard',
                            $filter->toArray()
                        )),
                    ],
                ),
            ],
        );
    }

    private function getAction(Licence $licence, ?string $referer): LinkView
    {
        return new LinkView(
            label: 'Valider',
            url: $this->urlContextService->generateUrl('admin_coverage_validate', ['licence' => $licence->getId(), ], $referer),
            icon: 'lucide:square-check-big',
            variant: ColorVariant::SUCCESS,
            size: Size::SM,
            title: 'Valider l\'asssurance',
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT)
            ],
        );
    }
}
