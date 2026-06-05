<?php

declare(strict_types=1);

namespace App\Mapper\Registration;

use App\Dto\BadgeDto;
use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\DropdownItemDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\RegistrationFilter;
use App\Dto\HtmlAttributDto;
use App\Dto\LabelDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
use App\Entity\Licence;
use App\Entity\Member;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\Registration\RegistrationDropdownMapper;
use App\Service\Filter\FilterConfigInterface;
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
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        RegistrationFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListDto {
        $items = [];
        /** @var Member $entity */
        foreach ($entities as $entity) {
            $identity = $entity->getIdentity();
            $licence = $entity->getLastLicence();
            $state = $licence->getState();
            $items[] = new ListItemDto(
                labels: [
                    new LabelDto($identity->getFullName()),
                ],
                indicators: $this->getIndicators($entity),
                status: new BadgeDto(
                    value:$state->shortTrans($this->translator), 
                    variant: $state->variant(),
                ),
                dropdown: $this->registrationDropdownMapper->mapToView($entity),
                url: $this->urlGenerator->generate("admin_user", ['user' => $entity->getId()]),
                action: $this->getAction($licence, $currentPage, $filter),
            );
        }

        return new ListDto(
            items: $items,
            settings: $this->settings(),
            tools: $this->tools($filter),
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonDto(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::SHEET_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
        );
    }

    public function settings(): DropdownDto
    {
        return $this->dropdownSettingsMapper->mapToView('REGISTRATION', RoundedVariant::ROUNDED, [
            new ButtonDto(
                label: 'Étapes des inscriptions',
                url: $this->urlGenerator->generate('admin_registration_step_list'),
                variant: ColorVariant::DROPDOWN,
            ),
            new ButtonDto(
                label: 'Gestions des autorisations',
                url: $this->urlGenerator->generate('admin_agreement_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    public function tools(RegistrationFilter $filter): ?DropdownDto
    {
        return new DropdownDto(
            variant: DropdownVariant::GOST,
            rounded: RoundedVariant::ROUNDED_NONE,
            menuItems: [
                new ButtonDto(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_registrations_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributDto('data-action', 'click->dropdown#close'),
                        new HtmlAttributDto('data-turbo', 'false')
                    ]
                )
            ],
            actionItems: [
                new DropdownItemDto(
                    label: 'Copier les emails de la séléction',
                    icon: 'lucide:clipboard-type',
                    htmlAttributes: [
                        new HtmlAttributDto('data-controller', 'email-to-clipboard'),
                        new HtmlAttributDto('data-action', 'click->email-to-clipboard#emailToClipboard click->dropdown#close'),
                        new HtmlAttributDto('data-email-to-clipboard-url-value', $this->urlGenerator->generate(
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
        $indicators[] = new BadgeDto(
            value: $licence->getState()->icon(),
            variant: $licence->getState()->variant(),
            size: Size::ICON,
        );

        if (!$entity->getLastLicence()->getState()->isYearly()) {
            $indicators[] = new BadgeDto(
                value: sprintf('%s/3', $this->userService->trialSessionsPresent($licence, $entity)),
                variant: ColorVariant::DEFAULT,
            );
        }
        $level = $entity->getLevel();
        $indicators[] = new BadgeDto(
            value:$level->getTitle(), 
            color: $level->getColor(),
        );

        return $indicators;
    }

    private function getAction(Licence $licence, ?int $currentPage, RegistrationFilter $filter): ?ButtonDto
    {
        $state = $licence->getState();
        $params = [
            'licence' => $licence->getId(),
        ];
        if ($filterHash = $filter->toEncodedString($currentPage)) {
            $params['filter'] = $filterHash;
        }

        return match (true) {
            $state->toValidate() => new ButtonDto(
                label: 'Reçu',
                url: $this->urlGenerator->generate('admin_registration_receive', $params),
                icon: 'lucide:square-check-big',
                variant: ColorVariant::SUCCESS,
                title: 'Réceptionner le dossier d\'inscription',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT)
                ],
            ),
            $state->toRegister() => new ButtonDto(
                label: 'Inscrit',
                url: $this->urlGenerator->generate('admin_registration_register', $params),
                icon: 'lucide:square-check-big',
                variant: ColorVariant::SUCCESS,
                title: 'Inscrire à la FFvélo',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT)
                ],
            ),
            default => null
        };
    }
}
