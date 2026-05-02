<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\DropdownItemDto;
use App\Dto\HtmlAttributDto;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\OrderHeader;
use App\Entity\Product;
use App\Entity\Session;
use App\Entity\Survey;
use App\Entity\User;
use App\Repository\LicenceAgreementRepository;
use App\Service\LicenceAgreementService;
use App\Service\SessionService;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DropdownMapper
{
    public function __construct(
        private SessionService $sessionService,
        private LicenceAgreementRepository $licenceAgreementRepository,
        private LicenceAgreementService $licenceAgreementService,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }
    public function fromUser(User $user): DropdownDto
    {
        return new DropdownDto(
            title: $user->getIdentity()->getFullName(),
            menuItems: $this->getMenuItemsfromUser($user),
        );
    }

    public function getMenuItemsfromUser(User $user): array
    {
        $menuItems = [];
        $level = $user->getLevel();
        if ($this->security->isGranted('USER_LIST') && $level?->getType() === Level::TYPE_SCHOOL_MEMBER) {
            $menuItems[] = new ButtonDto(
                label: 'Compétences',
                url: $this->urlGenerator->generate('admin_member_skill_edit', ['member' => $user->getId()]),
                icon: 'lucide:graduation-cap',
            );
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = new ButtonDto(
                label: 'Participation',
                url: $this->urlGenerator->generate('admin_user_participation', ['user' => $user->getId()]),
                icon: 'lucide:chart-line',
            );
            $menuItems[] = new ButtonDto(
                label: 'Attestation d\'inscription CE',
                url: $this->urlGenerator->generate('admin_user_certificate', ['member' => $user->getId()]),
                icon: 'lucide:file-user',
            );
            if ($level?->isAccompanyingCertificat()) {
                $menuItems[] = new ButtonDto(
                    label: 'Attestation adulte accompagnateur',
                    url: $this->urlGenerator->generate('admin_user_accompanying_certificate', ['member' => $user->getId()]),
                    icon: 'lucide:file-terminal',
                );
            }
            if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
                $menuItems[] = new ButtonDto(
                    label: 'Se connecter en tant que',
                    url: $this->urlGenerator->generate('home', ['_switch_user' => $user->getLicenceNumber()]),
                    icon: 'lucide:arrow-left-right',
                );
            }
        }

        return $menuItems;
    }

    public function fromSession(Session $session): DropdownDto
    {
        $user = $session->getUser();
        $dropdown = $this->fromUser($user);
        $infoItems = [];
        if (AvailabilityEnum::NONE !== $session->getAvailability()) {
            $availability = $this->sessionService->getAvailability($session->getAvailability());
            $infoItems[] = new DropdownItemDto(
                $availability['text'],
                $availability['class']['ux_icon'],
            );
        }

        if ($goingHomeAlone = $this->licenceAgreementRepository->findOneByUserAndAggrementId($user, 'BACK_HOME_ALONE')) {
            $goingHomeAloneHtml = $this->licenceAgreementService->toHtml($goingHomeAlone);
            $infoItems[] = new DropdownItemDto(
                $goingHomeAloneHtml['message'],
                $goingHomeAloneHtml['icon']
            );
        }

        $menuItems = $this->getMenuItemsfromUser($user);
        $cluster = $session->getCluster();
        $bikeRide = $cluster->getBikeRide();
        $isEditable = $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide);
        if ($isEditable && !$cluster->isComplete()) {
            if (in_array($session->getAvailability(), [AvailabilityEnum::NONE, AvailabilityEnum::AVAILABLE, AvailabilityEnum::REGISTERED])) {
                $menuItems[] = new ButtonDto(
                    label: 'Changer de groupe',
                    url: $this->urlGenerator->generate('admin_bike_ride_switch_cluster', ['session' => $session->getId()]),
                    icon: 'lucide:refresh-cw',
                );
            }
            $menuItems[] = new ButtonDto(
                label: 'Supprimer',
                url: $this->urlGenerator->generate('admin_session_delete', ['session' => $session->getId()]),
                icon: 'lucide:delete',
            );
        }

        return new DropdownDto(
            title: $user->getIdentity()->getFullName(),
            infoItems: $infoItems,
            menuItems: $menuItems,
        );
    }

    public function fromLastLicence(Licence $licence): DropdownDto
    {
        
        $user = $licence->getUser();
        $menuItems = $this->getMenuItemsfromUser($user);
        if ($licence->getState()->toValidate()) {
            $menuItems[] = new ButtonDto(
                label: 'Inscription incompète',
                url: $this->urlGenerator->generate('admin_registration_reject', ['licence' => $licence->getId()]),
                icon: 'lucide:message-circle-warning',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                ],
            );
            $menuItems[] = new ButtonDto(
                label: 'Supprimer l\'inscription',
                url: $this->urlGenerator->generate('admin_delete_licence', ['licence' => $licence->getId()]),
                icon: 'lucide:delete',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                ],
            );
        }
        
        return new DropdownDto(
            title: $user->getIdentity()->getFullName(),
            menuItems: $menuItems,
        );
    }

    public function fromBikeRide(BikeRide $bikeRide): DropdownDto
    {
        $menuItems = [];
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = new ButtonDto(
                label: 'Modifier',
                url: $this->urlGenerator->generate('admin_bike_ride_edit', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:pencil',
            );
            if ($bikeRide->getStartAt() > new DateTimeImmutable()) {
                $menuItems[] = new ButtonDto(
                    label: 'Annuler',
                    url: $this->urlGenerator->generate('admin_bike_ride_delete', ['bikeRide' => $bikeRide->getId()]),
                    icon: 'lucide:delete',
                    htmlAttributes: [
                        new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                    ],
                );
            }
            $menuItems[] = new ButtonDto(
                label: 'Exporter la séance',
                url: $this->urlGenerator->generate('admin_bike_ride_export', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:file-down',
            );
        }
        if ($this->security->isGranted('SUMMARY_LIST')) {
            $menuItems[] = new ButtonDto(
                label: 'Actualités',
                url: $this->urlGenerator->generate('admin_summary_list', ['bikeRide' => $bikeRide->getId()]),
                icon: 'lucide:image',
            );
        }
        $actionItems = [];
        if ($bikeRide->getBikeRideType()->isPublic()) {
            $actionItems = new DropdownItemDto(
                label: 'Copier l\'url',
                icon: 'lucide:clipboard-copy',
                htmlAttributes: [
                    new HtmlAttributDto(
                        'data-clipboard-url-value',
                        $this->urlGenerator->generate(
                            'bike_ride_detail',
                            ['bikeRide' => $bikeRide->getId(), 'slug' => $bikeRide->getTitle()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                    )),
                    new HtmlAttributDto('data-controller', 'clipboard'),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ]
            );
        }
                                                         
        return new DropdownDto(
            title: $bikeRide->__toString(),
            menuItems: $menuItems,
            actionItems: $actionItems,
        );
    }

    public function fromBikeRideType(BikeRideType $bikeRideType): DropdownDto
    {
        return new DropdownDto(
            menuItems: [
                new ButtonDto(
                    label: 'Modifier',
                    url: $this->urlGenerator->generate('admin_bike_ride_type_edit', ['bikeRideType' => $bikeRideType->getId()]),
                    icon: 'lucide:pencil',
                ),
            ],
        );
    }

    public function fromSurveyForList(Survey $survey): DropdownDto
    {
        $menuItems[] = new ButtonDto(
            label: 'Exporter',
            url: $this->urlGenerator->generate('admin_survey_export', ['survey' => $survey->getId()]),
            icon: 'lucide:file-down',
        );
        $menuItems[] = new ButtonDto(
            label: 'Dupliquer',
            url: $this->urlGenerator->generate('admin_survey_copy', ['survey' => $survey->getId()]),
            icon: 'lucide:copy-plus',
        );
        if (!$survey->isDisabled()) {
            $menuItems[] = new ButtonDto(
                label: 'Modifier',
                url: $this->urlGenerator->generate('admin_survey_edit', ['survey' => $survey->getId()]),
                icon: 'lucide:pencil',
            );
            $menuItems[] = new ButtonDto(
                label: 'Cloturer',
                url: $this->urlGenerator->generate('admin_survey_disable', ['survey' => $survey->getId()]),
                icon: 'lucide:toggle-left',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                ],
            );
        }
        $menuItems[] = new ButtonDto(
            label: 'Supprimer',
            url: $this->urlGenerator->generate('admin_survey_delete', ['survey' => $survey->getId()]),
            icon: 'lucide:delete',
            htmlAttributes: [
                new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
            ],
        );

        return new DropdownDto(
            title: $survey->getTitle(),
            menuItems: $menuItems,
            actionItems: [
                new DropdownItemDto(
                    label: 'Copier l\'url',
                    icon: 'lucide:clipboard-copy',
                    htmlAttributes: [
                        new HtmlAttributDto(
                            'data-clipboard-url-value',
                            $this->urlGenerator->generate('survey', 
                                ['survey' => $survey->getId()], UrlGeneratorInterface::ABSOLUTE_URL
                            ),
                        ),
                        new HtmlAttributDto('data-controller', 'clipboard'),
                        new HtmlAttributDto('data-action', 'click->dropdown#close'),
                    ],
                ),
            ],
        );
    }

    public function fromOrder(OrderHeader $order): DropdownDto
    {
        return new DropdownDto(
            menuItems: [
                new ButtonDto(
                    label: 'Supprimer',
                    url: $this->urlGenerator->generate('order_delete', ['survey' => $order->getId()]),
                    icon: 'lucide:delete',
                    htmlAttributes: [
                        new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                    ],
                ),
            ]
        );
    }
}
