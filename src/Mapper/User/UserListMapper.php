<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\DropdownItemView;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\UserFilter;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListView;
use App\Dto\View\ListItemView;
use App\Entity\Member;
use App\Entity\User;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\WikiMapper;
use App\Service\Filter\FilterConfigInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private UserDropdownMapper $userDropdownMapper,
        private PaginatorMapper $paginatorMapper,
        private WikiMapper $wikiMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        UserFilter $filter,
        FilterConfigInterface $filterConfig
    ): ListView {
        $items = [];
        /** @var Member $entity */
        foreach ($entities as $entity) {
            $identity = $entity->getIdentity();
            $level = $entity->getLevel();
            $items[] = new ListItemView(
                labels: [
                    new LabelView($identity->getFullName()),
                ],
                indicators: $this->getIndicators($entity),
                status: new BadgeView(
                    value:$level->getTitle(), 
                    color: $level->getColor(),
                ),
                dropdown: $this->userDropdownMapper->mapToView($entity),
                url: $this->urlGenerator->generate("admin_user", ['user' => $entity->getId()]),
                gridTemplateContent: 'grid-cols-1 lg:grid-cols-[2fr_1fr]',
                gridTemplateBadges: 'grid-cols-[auto_160px]',
            );
        }

        return new ListView(
            id: 'users_container',
            title: 'Adhérents ',
            description: 'Administration des adhérents du club',
            items: $items,
            settings: $this->settings(),
            tools: $this->getTools($filter),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_user_list'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            wiki: $this->wikiMapper->mapToView('adhérents', RoundedVariant::ROUNDED_START),
        );
    }


    private function settings(): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('USER', RoundedVariant::ROUNDED_END, [
            new ButtonView(
                label: 'Niveaux',
                url: $this->urlGenerator->generate('admin_levels'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonView(
                label: 'Compétences',
                url: $this->urlGenerator->generate('admin_skill_list'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonView(
                label: 'Roles du bureau et comité',
                url: $this->urlGenerator->generate('admin_board_role_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function getIndicators(User $entity): array
    {
        $indicators = [];
        $indicators[] = new BadgeView(
            value: $entity->getLevel()->getType()->getIcon(),
            variant: ColorVariant::ACCENT,
            size: Size::ICON
        );

        $indicators[] = new BadgeView(
            value: (string) $entity->getLastLicence()->getSeason(),
            variant: ColorVariant::AMBER,
        );

        return $indicators;
    }

    private function getTools(UserFilter $filter): DropdownView
    {
        return new DropdownView(
            variant: DropdownVariant::GOST,
            rounded: RoundedVariant::ROUNDED_END,
            menuItems: [
                new ButtonView(
                    label: 'Exporter la sélection',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_members_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    htmlAttributes: [
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                        new HtmlAttributView('data-turbo', 'false')
                    ],
                ),
                new ButtonView(
                    label: 'Exporter les évaluations de la sélection',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_user_skill_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    htmlAttributes: [
                        new HtmlAttributView('data-action', 'click->dropdown#close')
                    ],
                ),
                new ButtonView(
                    label: 'Synthèse par saison',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_overview_season'),
                    icon: 'lucide:chart-scatter',
                    htmlAttributes: [
                        new HtmlAttributView('data-action', 'click->dropdown#close')
                    ],
                ),
            ],
            actionItems: [
                new DropdownItemView(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributView('data-controller', 'email-to-clipboard'),
                        new HtmlAttributView('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                        new HtmlAttributView('data-email-to-clipboard-url-value', $this->urlGenerator->generate(
                            'admin_members_email_to_clipboard', 
                            $filter->toArray()
                        )),
                    ],
                ),
            ],
        );
    }
}
