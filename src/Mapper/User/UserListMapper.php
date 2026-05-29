<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Dto\BadgeDto;
use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\DropdownItemDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\UserFilter;
use App\Dto\HtmlAttributDto;
use App\Dto\LabelDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
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
    ): ListDto {
        $items = [];
        /** @var Member $entity */
        foreach ($entities as $entity) {
            $identity = $entity->getIdentity();
            $level = $entity->getLevel();
            $items[] = new ListItemDto(
                labels: [
                    new LabelDto($identity->getFullName()),
                ],
                indicators: $this->getIndicators($entity),
                status: new BadgeDto(
                    value:$level->getTitle(), 
                    color: $level->getColor(),
                ),
                dropdown: $this->userDropdownMapper->mapToView($entity),
                url: $this->urlGenerator->generate("admin_user", ['user' => $entity->getId()]),
            );
        }

        return new ListDto(
            items: $items,
            settings: $this->settings(),
            tools: $this->getTools($filter),
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonDto(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_user_list'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::SHEET_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            wiki: $this->wikiMapper->mapToView('adhérents', RoundedVariant::ROUNDED_START),
        );
    }


    private function settings(): DropdownDto
    {
        return $this->dropdownSettingsMapper->mapToView('USER', RoundedVariant::ROUNDED_END, [
            new ButtonDto(
                label: 'Niveaux',
                url: $this->urlGenerator->generate('admin_levels'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonDto(
                label: 'Compétences',
                url: $this->urlGenerator->generate('admin_skill_list'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonDto(
                label: 'Roles du bureau et comité',
                url: $this->urlGenerator->generate('admin_board_role_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function getIndicators(User $entity): array
    {
        $indicators = [];
        $indicators[] = new BadgeDto(
            value: $entity->getLevel()->getType()->getIcon(),
            variant: ColorVariant::ACCENT,
            size: Size::ICON
        );

        $indicators[] = new BadgeDto(
            value: (string) $entity->getLastLicence()->getSeason(),
            variant: ColorVariant::AMBER,
        );

        return $indicators;
    }

    private function getTools(UserFilter $filter): DropdownDto
    {
        return new DropdownDto(
            variant: DropdownVariant::GOST,
            rounded: RoundedVariant::ROUNDED_END,
            menuItems: [
                new ButtonDto(
                    label: 'Exporter la sélection',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_members_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    htmlAttributes: [
                        new HtmlAttributDto('data-action', 'click->dropdown#close'),
                        new HtmlAttributDto('data-turbo', 'false')
                    ],
                ),
                new ButtonDto(
                    label: 'Exporter les évaluations de la sélection',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_user_skill_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    htmlAttributes: [
                        new HtmlAttributDto('data-action', 'click->dropdown#close')
                    ],
                ),
                new ButtonDto(
                    label: 'Synthèse par saison',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_overview_season'),
                    icon: 'lucide:chart-scatter',
                    htmlAttributes: [
                        new HtmlAttributDto('data-action', 'click->dropdown#close')
                    ],
                ),
            ],
            actionItems: [
                new DropdownItemDto(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributDto('data-controller', 'email-to-clipboard'),
                        new HtmlAttributDto('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                        new HtmlAttributDto('data-email-to-clipboard-url-value', $this->urlGenerator->generate(
                            'admin_members_email_to_clipboard', 
                            $filter->toArray()
                        )),
                    ],
                ),
            ],
        );
    }
}
