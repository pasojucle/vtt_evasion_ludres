<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\UserFilter;
use App\Dto\View\DropdownItemView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\User;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\WikiMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UserMapper $userMapper,
        private WikiMapper $wikiMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        UserFilter $filter,
        FilterConfigInterface $filterConfig
    ): ListView {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'user',
            title: 'Adhérents ',
            description: 'Administration des adhérents du club',
            items: array_map(fn (User $user) => $this->userMapper->mapToView($user, $targetUrl), iterator_to_array($entities)),
            dropdown: $this->settings($targetUrl, $filter),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_user_list'], $filter->toQueryParams())),
                size: Size::ICON,
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getAdvancedFields(), $filterConfig->getRouteName()),
        );
    }

    private function settings(string $targetUrl, UserFilter $filter): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView(
            section: 'USER',
            referer: $targetUrl,
            rounded: RoundedVariant::ROUNDED,
            menuItems: [
                new LinkView(
                    label: 'Exporter la sélection',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_members_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    htmlAttributes: [
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                        new HtmlAttributeView('data-turbo', 'false')
                    ],
                ),
                new LinkView(
                    label: 'Exporter les évaluations de la sélection',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_user_skill_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    htmlAttributes: [
                        new HtmlAttributeView('data-action', 'click->dropdown#close')
                    ],
                ),
                new LinkView(
                    label: 'Synthèse par saison',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_overview_season_list'),
                    icon: 'lucide:chart-scatter',
                    htmlAttributes: [
                        new HtmlAttributeView('data-action', 'click->dropdown#close')
                    ],
                ),
                $this->wikiMapper->mapToView('adhérents'),
                new LinkView(
                    label: 'Niveaux',
                    icon: 'lucide:settings',
                    url: $this->urlContextService->generateUrl('admin_level_list', [], $targetUrl),
                    variant: ColorVariant::DROPDOWN,
                ),
                new LinkView(
                    label: 'Compétences',
                    icon: 'lucide:settings',
                    url: $this->urlContextService->generateUrl('admin_skill_list', [], $targetUrl),
                    variant: ColorVariant::DROPDOWN,
                ),
                new LinkView(
                    label: 'Roles du bureau et comité',
                    icon: 'lucide:settings',
                    url: $this->urlContextService->generateUrl('admin_board_role_list', [], $targetUrl),
                    variant: ColorVariant::DROPDOWN,
                ),
            ],
            actionItems: [
                new DropdownItemView(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributeView('data-controller', 'email-to-clipboard'),
                        new HtmlAttributeView('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                        new HtmlAttributeView('data-email-to-clipboard-url-value', $this->urlGenerator->generate(
                            'admin_members_email_to_clipboard',
                            $filter->toArray()
                        )),
                    ],
                ),
            ],
        );
    }
}
