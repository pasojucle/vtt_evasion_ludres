<?php

declare(strict_types=1);

namespace App\Mapper\Registration;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\RegistrationFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownItemView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\Interface\ListActionViewInterface;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\Licence;
use App\Entity\Member;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\Registration\RegistrationDropdownMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\SeasonService;
use App\Service\UrlContextService;
use App\Service\UserService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private RegistrationDropdownMapper $registrationDropdownMapper,
        private UserService $userService,
        private TranslatorInterface $translator,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        RegistrationFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams());

        $items = [];
        /** @var Member $entity */
        foreach ($entities as $entity) {
            $identity = $entity->getIdentity();
            $licence = $entity->getLastLicence();
            $state = $licence->getState();
            $items[] = new ListItemView(
                labels: [
                    new LabelView($identity->getFullName()),
                ],
                indicators: $this->getIndicators($entity),
                status: new BadgeView(
                    value:$state->shortTrans($this->translator),
                    variant: $state->variant(),
                ),
                dropdown: $this->registrationDropdownMapper->mapToView($entity, $referer),
                url: $this->urlGenerator->generate("admin_user", ['user' => $entity->getId()]),
                action: $this->getAction($licence, $currentPage, $filter),
                gridTemplateRow: 'grid-cols-1 lg:grid-cols-[1fr_112px]',
                gridTemplateContent: 'grid-cols-1 lg:grid-cols-[1fr_2fr]',
                gridTemplateBadges: 'grid-cols-[auto_90px] lg:grid-cols-[auto_180px]',
            );
        }

        return new ListView(
            name: 'registration',
            title: 'Inscriptions',
            description: sprintf('Administration des inscriptions pour la saison %s.', $this->seasonService->getCurrentSeason()),
            items: $items,
            settings: $this->settings($referer),
            tools: $this->tools($filter),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
        );
    }

    public function settings(string $referer): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('REGISTRATION', $referer, RoundedVariant::ROUNDED, [
            new ButtonView(
                label: 'Étapes des inscriptions',
                url: $this->urlGenerator->generate('admin_registration_step_list'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonView(
                label: 'Gestions des autorisations',
                url: $this->urlGenerator->generate('admin_agreement_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    public function tools(RegistrationFilter $filter): ?DropdownView
    {
        return new DropdownView(
            variant: DropdownVariant::GOST,
            rounded: RoundedVariant::ROUNDED_NONE,
            menuItems: [
                new ButtonView(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_registrations_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                        new HtmlAttributView('data-turbo', 'false')
                    ]
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
                            'admin_registrations_email_to_clipboard',
                            $filter->toArray()
                        )),
                    ],
                ),
            ],
        );
    }

    private function getIndicators(Member $entity): array
    {
        $indicators = [];
        $licence = $entity->getLastLicence();
        $indicators[] = new BadgeView(
            value: $licence->getState()->icon(),
            variant: $licence->getState()->variant(),
            size: Size::ICON,
        );

        if (!$entity->getLastLicence()->getState()->isYearly()) {
            $indicators[] = new BadgeView(
                value: sprintf('%s/3', $this->userService->trialSessionsPresent($licence, $entity)),
                variant: ColorVariant::DEFAULT,
            );
        }
        $level = $entity->getLevel();
        $indicators[] = new BadgeView(
            value:$level->getTitle(),
            color: $level->getColor(),
        );

        return $indicators;
    }

    private function getAction(Licence $licence, ?int $currentPage, RegistrationFilter $filter): ?ListActionViewInterface
    {
        $state = $licence->getState();
        $params = [
            'licence' => $licence->getId(),
        ];
        if ($filterHash = $filter->toEncodedString($currentPage)) {
            $params['filter'] = $filterHash;
        }

        return match (true) {
            $state->toValidate() => new ButtonView(
                label: 'Reçu',
                url: $this->urlGenerator->generate('admin_registration_receive', $params),
                icon: 'lucide:square-check-big',
                variant: ColorVariant::SUCCESS,
                size: Size::SM,
                title: 'Réceptionner le dossier d\'inscription',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT)
                ],
            ),
            $state->toRegister() => new ButtonView(
                label: 'Inscrit',
                url: $this->urlGenerator->generate('admin_registration_register', $params),
                icon: 'lucide:square-check-big',
                variant: ColorVariant::SUCCESS,
                size: Size::SM,
                title: 'Inscrire à la FFvélo',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT)
                ],
            ),
            default => null,
        };
    }
}
