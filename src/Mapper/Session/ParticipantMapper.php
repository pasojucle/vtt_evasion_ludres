<?php

declare(strict_types=1);

namespace App\Mapper\Session;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;

use App\Dto\View\BadgeView;
use App\Dto\View\Cluster\ParticipantView;
use App\Dto\View\DropdownItemView;
use App\Dto\View\DropdownView;
use App\Dto\View\LinkView;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\LevelType;
use App\Entity\Licence;
use App\Entity\LicenceAgreement;
use App\Entity\Member;
use App\Entity\Session;
use App\Mapper\Level\LevelBadgeMapper;
use App\Mapper\LicenceAuthorization\LicenceAuthorizationBadgeMapper;
use App\Service\UrlContextService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    ){

    }

    public function mapToView(
        Session $session, 
        array $authorizations, 
        int $participations,
        bool $isClusterComplete,
        bool $isEditable,
        int $currentSeason,
        ?string $referer
    ): ParticipantView {
        $user = $session->getUser();
        $identity = $user->getIdentity();
        $level = $user->getLevel();
        $licences = $user->getLicences();
        $lastLicence = $licences->findFirst(function(int $key, Licence $licence) use ($currentSeason) {
            dump(sprintf('%s -%s', $licence->getSeason(), $currentSeason));
            return $licence->getSeason() === $currentSeason;
        });
        $lastLicence = $licences->findFirst(fn(int $key, Licence $licence) => $licence->getSeason() === $currentSeason);
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
            $isPendingReceipt = $lastLicence?->isPendingReceipt($currentSeason, $licences->count);
        }


        return new ParticipantView(
            sessionId: $session->getId(),
            url: $this->urlContextService->generateUrl('admin_user_show', ['user' => $user->getId()], $referer),
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
                $referer
            ),
            indicators: $indicators,
            action: null,
            status: ($isClusterComplete)
                ? ($isPresent)  
                    ? new BadgeView(
                        value: 'Présent' ,
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
                $menuItems[] = new LinkView(
                    label: 'Changer de groupe',
                    url: $this->urlGenerator->generate('admin_bike_ride_switch_cluster', ['session' => $session->getId()]),
                    icon: 'lucide:refresh-cw',
                );
            }
            $menuItems[] = new LinkView(
                label: 'Supprimer',
                url: $this->urlGenerator->generate('admin_session_delete', ['session' => $session->getId()]),
                icon: 'lucide:delete',
            );
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
}
// Dropdown accompanyingCertificat

    // warnings $isEndTesting, $mustProvideRegistration
    // 'id' => $session->getId(),
    //                 'availability' => $this->sessionService->getAvailability($session->getAvailability()),
    //                 'user' => [
    //                     'id' => $user->getId(),
    //                     'member' => [
    //                         'fullName' => $identity->getName() . ' ' . $identity->getFirstName(),
    //                     ],
    //                     'level' => [
    //                         'colors' => $this->levelService->getColors($level?->getColor()),
    //                         'title' => $level?->getTitle(),
    //                         'type' => $level?->getType(),
    //                         'accompanyingCertificat' => $level?->isAccompanyingCertificat(),
    //                     ],
    //                     'lastLicence' => [
    //                         'authorizations' => $licencesAgreements
    //                     ],
    //                     'health' => [
    //                         'content' => $health,
    //                     ],
    //                     'isEndTesting' => $isEndTesting,
    //                     'mustProvideRegistration' => $mustProvideRegistration,
    //                     'licenceNumber' => $user->getLicenceNumber(),
    //                     'dropdown' => $this->dropdownMapper->fromSession($session),
    //                 ],
    //                 'userIsOnSite' => $session->isPresent(),
    //                 'practice' => $bikeRideType->isDisplayBikeKind() ? $session->getPractice()->toBadge($this->translator) : null,
    //                 'bikeType' => $session->getBikeType()->toBadge($this->translator),