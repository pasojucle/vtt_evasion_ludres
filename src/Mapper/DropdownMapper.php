<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Dto\View\LinkView;
use App\Dto\View\DropdownItemView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Entity\BikeRideType;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\LevelType;
use App\Entity\Licence;
use App\Entity\OrderHeader;
use App\Entity\Session;
use App\Entity\Survey;
use App\Entity\User;
use App\Repository\LicenceAgreementRepository;
use App\Service\LicenceAgreementService;
use App\Service\SessionService;
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
    public function fromUser(User $user): DropdownView
    {
        return new DropdownView(
            title: $user->getIdentity()->getFullName(),
            menuItems: $this->getMenuItemsfromUser($user),
        );
    }

    public function getMenuItemsfromUser(User $user): array
    {
        $menuItems = [];
        $level = $user->getLevel();
        if ($this->security->isGranted('USER_LIST') && $level?->getType() === LevelType::SCHOOL) {
            $menuItems[] = new LinkView(
                label: 'Compétences',
                url: $this->urlGenerator->generate('admin_member_skill_edit', ['member' => $user->getId()]),
                icon: 'lucide:graduation-cap',
            );
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = new LinkView(
                label: 'Participation',
                url: $this->urlGenerator->generate('admin_user_participation', ['user' => $user->getId()]),
                icon: 'lucide:chart-line',
            );
            $menuItems[] = new LinkView(
                label: 'Attestation d\'inscription CE',
                url: $this->urlGenerator->generate('admin_user_certificate', ['member' => $user->getId()]),
                icon: 'lucide:file-user',
            );
            if ($level?->isAccompanyingCertificat()) {
                $menuItems[] = new LinkView(
                    label: 'Attestation adulte accompagnateur',
                    url: $this->urlGenerator->generate('admin_user_accompanying_certificate', ['member' => $user->getId()]),
                    icon: 'lucide:file-terminal',
                );
            }
            if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
                $menuItems[] = new LinkView(
                    label: 'Se connecter en tant que',
                    url: $this->urlGenerator->generate('home', ['_switch_user' => $user->getLicenceNumber()]),
                    icon: 'lucide:arrow-left-right',
                );
            }
        }

        return $menuItems;
    }

    public function fromSession(Session $session): DropdownView
    {
        $user = $session->getUser();
        $dropdown = $this->fromUser($user);
        $infoItems = [];
        if (AvailabilityEnum::NONE !== $session->getAvailability()) {
            $availability = $this->sessionService->getAvailability($session->getAvailability());
            $infoItems[] = new DropdownItemView(
                $availability['text'],
                $availability['class']['ux_icon'],
            );
        }

        if ($goingHomeAlone = $this->licenceAgreementRepository->findOneByUserAndAggrementId($user, 'BACK_HOME_ALONE')) {
            $goingHomeAloneHtml = $this->licenceAgreementService->toHtml($goingHomeAlone);
            $infoItems[] = new DropdownItemView(
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

        return new DropdownView(
            title: $user->getIdentity()->getFullName(),
            infoItems: $infoItems,
            menuItems: $menuItems,
        );
    }

    public function fromLastLicence(Licence $licence): DropdownView
    {
        $user = $licence->getUser();
        $menuItems = $this->getMenuItemsfromUser($user);
        if ($licence->getState()->toValidate()) {
            $menuItems[] = new LinkView(
                label: 'Inscription incompète',
                url: $this->urlGenerator->generate('admin_registration_reject', ['licence' => $licence->getId()]),
                icon: 'lucide:message-circle-warning',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                ],
            );
            $menuItems[] = new LinkView(
                label: 'Supprimer l\'inscription',
                url: $this->urlGenerator->generate('admin_licence_delete', ['licence' => $licence->getId()]),
                icon: 'lucide:delete',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                ],
            );
        }
        
        return new DropdownView(
            title: $user->getIdentity()->getFullName(),
            menuItems: $menuItems,
        );
    }

    public function fromBikeRideType(BikeRideType $bikeRideType): DropdownView
    {
        return new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlGenerator->generate('admin_bike_ride_type_edit', ['bikeRideType' => $bikeRideType->getId()]),
                    icon: 'lucide:pencil',
                ),
            ],
        );
    }

    public function fromSurveyForList(Survey $survey): DropdownView
    {
        $menuItems[] = new LinkView(
            label: 'Exporter',
            url: $this->urlGenerator->generate('admin_survey_export', ['survey' => $survey->getId()]),
            icon: 'lucide:file-down',
        );
        $menuItems[] = new LinkView(
            label: 'Dupliquer',
            url: $this->urlGenerator->generate('admin_survey_copy', ['survey' => $survey->getId()]),
            icon: 'lucide:copy-plus',
        );
        if (!$survey->isDisabled()) {
            $menuItems[] = new LinkView(
                label: 'Modifier',
                url: $this->urlGenerator->generate('admin_survey_edit', ['survey' => $survey->getId()]),
                icon: 'lucide:pencil',
            );
            $menuItems[] = new LinkView(
                label: 'Cloturer',
                url: $this->urlGenerator->generate('admin_survey_disable', ['survey' => $survey->getId()]),
                icon: 'lucide:toggle-left',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                ],
            );
        }
        $menuItems[] = new LinkView(
            label: 'Supprimer',
            url: $this->urlGenerator->generate('admin_survey_delete', ['survey' => $survey->getId()]),
            icon: 'lucide:delete',
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
            ],
        );

        return new DropdownView(
            title: $survey->getTitle(),
            menuItems: $menuItems,
            actionItems: [
                new DropdownItemView(
                    label: 'Copier l\'url',
                    icon: 'lucide:clipboard-copy',
                    htmlAttributes: [
                        new HtmlAttributView(
                            'data-clipboard-url-value',
                            $this->urlGenerator->generate(
                                'survey',
                                ['survey' => $survey->getId()],
                                UrlGeneratorInterface::ABSOLUTE_URL
                            ),
                        ),
                        new HtmlAttributView('data-controller', 'clipboard'),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                ),
            ],
        );
    }

    public function fromOrder(OrderHeader $order): DropdownView
    {
        return new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Supprimer',
                    url: $this->urlGenerator->generate('order_delete', ['survey' => $order->getId()]),
                    icon: 'lucide:delete',
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                    ],
                ),
            ]
        );
    }
}
