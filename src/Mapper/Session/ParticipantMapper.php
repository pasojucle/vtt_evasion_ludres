<?php

declare(strict_types=1);

namespace App\Mapper\Session;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;

use App\Dto\View\BadgeView;
use App\Dto\View\Cluster\ParticipantView;
use App\Dto\View\DropdownItemView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LinkView;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\LevelType;
use App\Entity\Licence;
use App\Entity\LicenceAgreement;
use App\Entity\Member;
use App\Entity\Session;
use App\Mapper\Level\LevelBadgeMapper;
use App\Mapper\LicenceAuthorization\LicenceAuthorizationBadgeMapper;
use App\Service\CsrfTokenService;
use App\Service\UrlContextService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParticipantMapper
{
    public function __construct(
        private LevelBadgeMapper $levelBadgeMapper,
        private LicenceAuthorizationBadgeMapper $licenceAuthorizationBadgeMapper,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private Security $security,
        private UrlContextService $urlContextService,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
    ) {
    }

    public function mapToView(
        Session $session,
        array $authorizations,
        int $participations,
        bool $isClusterComplete,
        bool $isEditable,
        int $currentSeason,
        ?string $fallback
    ): ParticipantView {
        $user = $session->getUser();
        $identity = $user->getIdentity();
        $level = $user->getLevel();
        $licences = $user->getLicences();
        $isPendingReceipt = false;
        $lastLicence = $licences->findFirst(function (int $key, Licence $licence) use ($currentSeason) {
            return $licence->getSeason() === $currentSeason;
        });
        $lastLicence = $licences->findFirst(fn (int $key, Licence $licence) => $licence->getSeason() === $currentSeason);
        $availability = $session->getAvailability();
        $isPresent = $session->isPresent();

        $indicators = [];
        if ($user instanceof Member) {
            foreach ($user->getLastLicence()->getLicenceAuthorizations() as $licenceAgreement) {
                $indicators[] = $this->licenceAuthorizationBadgeMapper->mapToView($licenceAgreement);
            }
            if ($user->getHealth()->getContent()) {
                $indicators[] = new BadgeView(
                    value: 'lucide:stethoscope',
                    variant: ColorVariant::WARNING,
                    size: Size::ICON,
                );
            }
            if ($availability !== AvailabilityEnum::NONE) {
                $indicators[] = new BadgeView(
                    value: $availability->getIcon(),
                    variant: $availability->variant(),
                    size: Size::ICON,
                );
            }

            if ($lastLicence?->isEndTesting($participations)) {
                $indicators[] = new BadgeView(
                    value: 'lucide:file-clock',
                    variant: ColorVariant::DESTRUCTIVE,
                    size: Size::ICON,
                );
            }
            $isPendingReceipt = $lastLicence?->isPendingReceipt($currentSeason, $licences->count()) ?? false;
        }


        return new ParticipantView(
            sessionId: $session->getId(),
            url: $this->urlContextService->generateUrl('admin_user_show', ['user' => $user->getId()], $fallback),
            isPresent: $isPresent,
            isFramer: $level->getType() === LevelType::FRAME,
            userId: $user->getId(),
            fullName: $identity->getFullName(),
            level: $this->levelBadgeMapper->mapToView($level),
            levelType: ($level) ? new BadgeView(
                value: $level->getType()->getIcon(),
                size: Size::ICON,
            ) : null,
            dropdown: $this->dropdown(
                $session,
                $authorizations['BACK_HOME_ALONE'] ?? null,
                $isClusterComplete,
                $isEditable,
                $fallback
            ),
            indicators: $indicators,
            action: $this->getAction($session, $isPresent, $isClusterComplete, $isPendingReceipt),
            status: ($isClusterComplete)
                ? ($isPresent)
                    ? new BadgeView(
                        value: 'Présent',
                        variant: ColorVariant::SUCCESS,
                    )
                    : new BadgeView(
                        value: 'Absent',
                        variant: ColorVariant::DESTRUCTIVE,
                    )
                : null,
        );
    }

    private function dropdown(
        Session $session,
        ?LicenceAgreement $backHomeAuthorization,
        bool $isClusterComplete,
        bool $isEditable,
        ?string $referer,
    ): DropdownView {
        $user = $session->getUser();
        
        $infoItems = [];
        if (AvailabilityEnum::NONE !== $availability = $session->getAvailability()) {
            $infoItems[] = new DropdownItemView(
                $availability->trans($this->translator),
                $availability->getIcon(),
            );
        }

        if ($backHomeAuthorization) {
            $agreement = $backHomeAuthorization->getAgreement();
            $isAgreed = $backHomeAuthorization->isAgreed();
            $infoItems[] = ($isAgreed)
                ? new DropdownItemView(
                    $agreement->getAuthorizationMessage(),
                    $agreement->getAuthorizationIcon()
                )
                : new DropdownItemView(
                    $agreement->getRejectionMessage(),
                    $agreement->getRejectionIcon(),
                );
        }

        $menuItems = [];
        if ($isEditable && !$isClusterComplete) {
            if (in_array($session->getAvailability(), [AvailabilityEnum::NONE, AvailabilityEnum::AVAILABLE, AvailabilityEnum::REGISTERED])) {
                $cluster = $session->getCluster();
                $menuItems[] = new LinkView(
                    label: 'Changer de groupe',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_bike_ride_switch_cluster', ['cluster' => $cluster->getId(), 'session' => $session->getId()]),
                    icon: 'lucide:refresh-cw',
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                );
            }
            if (!$session->isPresent()) {
                $menuItems[] = new LinkView(
                    label: 'Supprimer',
                    variant: ColorVariant::DROPDOWN,
                    url: $this->urlGenerator->generate('admin_session_delete', ['session' => $session->getId()]),
                    icon: 'lucide:delete',
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                );
            }
        }
            
        if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $menuItems[] = new LinkView(
                label: 'Se connecter en tant que',
                variant: ColorVariant::DROPDOWN,
                url: $this->urlContextService->generateUrl('home', ['_switch_user' => $user->getLicenceNumber()], $referer),
                icon: 'lucide:arrow-left-right',
            );
        }


        return new DropdownView(
            title: $user->getIdentity()->getFullName(),
            infoItems: $infoItems,
            menuItems: $menuItems,
        );
    }

    private function getAction(Session $session, bool $isPresent, bool $isClusterComplete, bool $isPendingReceipt): ?LinkView
    {
        if ($isClusterComplete) {
            return null;
        }

        $tokenId = $this->csrfTokenService->getTokenId($session);
        $tokenValue = $this->csrfTokenManager->getToken($tokenId)->getValue();

        $url = $this->urlGenerator->generate('admin_session_toggle_present', [
            'session' => $session->getId(),
            'csrfToken' => $tokenValue,
        ]);
        if ($isPresent) {
            return new LinkView(
                url: $url,
                variant: ColorVariant::SUCCESS,
                size: Size::ICON,
                icon: 'lucide:square-check-big'
            );
        }
        if ($isPendingReceipt) {
            return new LinkView(
                url: $this->urlGenerator->generate('admin_session_message', [
                    'session' => $session->getId(),
                    'csrfToken' => $tokenValue,
                ]),
                variant: ColorVariant::WARNING,
                size: Size::ICON,
                icon: 'lucide:message-circle-question-mark',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT)
                ],
            );
        }

        return new LinkView(
            url: $url,
            variant: ColorVariant::OUTLINE,
            size: Size::ICON,
            icon: 'lucide:check'
        );
    }
}
