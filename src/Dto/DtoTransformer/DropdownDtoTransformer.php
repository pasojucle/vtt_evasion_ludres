<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Parameter;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\LicenceAgreementRepository;
use App\Repository\ParameterRepository;
use App\Service\LicenceAgreementService;
use App\Service\MessageService;
use App\Service\SessionService;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DropdownDtoTransformer
{
    public function __construct(
        private SessionService $sessionService,
        private LicenceAgreementRepository $licenceAgreementRepository,
        private LicenceAgreementService $licenceAgreementService,
        private ParameterRepository $parameterRepository,
        private MessageService $messageService,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
    )
    {
    }
    public function fromUser(User $user): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->title = $user->getIdentity()->getFullName();
        $level = $user->getLevel();
        if ($this->security->isGranted('USER_LIST') && $level?->getType() === Level::TYPE_SCHOOL_MEMBER) {
            $dropdown->addMenuItem(
                'Compétences',
                'lucide:graduation-cap',
                $this->urlGenerator->generate('admin_member_skill_edit', ['member' => $user->getId()]),
            );
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $dropdown->addMenuItem(
                'Participation',
                'lucide:chart-line',
                $this->urlGenerator->generate('admin_user_participation', ['user' => $user->getId()]),
            );
            $dropdown->addMenuItem(
                'Attestation d\'inscription CE',
                'lucide:file-user',
                $this->urlGenerator->generate('admin_user_certificate', ['member' => $user->getId()]),
            );
            if ($level?->isAccompanyingCertificat()) {
                $dropdown->addMenuItem(
                    'Attestation adulte accompagnateur',
                    'lucide:file-terminal',
                    $this->urlGenerator->generate('admin_user_accompanying_certificate', ['member' => $user->getId()]),
                );
            }
            if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
                $dropdown->addMenuItem(
                    'Se connecter en tant que',
                    'lucide:arrow-left-right',
                    $this->urlGenerator->generate('home', ['_switch_user' => $user->getLicenceNumber()]),
                );
            }
        }
      
        return $dropdown;
    }

    public function fromSession(Session $session): DropdownDto
    {
        $user = $session->getUser();
        $dropdown = $this->fromUser($user);
        if (AvailabilityEnum::NONE !== $session->getAvailability()) {
            $availability = $this->sessionService->getAvailability($session->getAvailability());
            $dropdown->addInfoItem(
                $availability['text'],
                $availability['class']['ux_icon'], 
            );
        }

        if ($goingHomeAlone = $this->licenceAgreementRepository->findOneByUserAndAggrementId($user, 'BACK_HOME_ALONE')) {
            $goingHomeAloneHtml = $this->licenceAgreementService->toHtml($goingHomeAlone);
            $dropdown->addInfoItem(
                $goingHomeAloneHtml['message'],
                $goingHomeAloneHtml['icon']
            );
        }
        $cluster = $session->getCluster();
        $bikeRide = $cluster->getBikeRide();
        $isEditable = $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide);
        if ($isEditable && !$cluster->isComplete()) {
            if (in_array($session->getAvailability(), [AvailabilityEnum::NONE, AvailabilityEnum::AVAILABLE, AvailabilityEnum::REGISTERED])) {
                $dropdown->addMenuItem(
                    'Changer de groupe',
                    'lucide:refresh-cw',
                    $this->urlGenerator->generate('admin_bike_ride_switch_cluster', ['session' => $session->getId()]),
                );
            }
            $dropdown->addMenuItem(
                'Supprimer',
                'lucide:delete',
                $this->urlGenerator->generate('admin_session_delete', ['session' => $session->getId()]),
            );
        }

        return $dropdown;
    }

    public function fromLastLicence(Licence $licence): DropdownDto
    {
        $user = $licence->getUser();
        $dropdown = $this->fromUser($user);
        if ($licence->getState()->toValidate()) {
            $dropdown->addMenuItem(
                'Inscription incompète',
                'lucide:message-circle-warning',
                $this->urlGenerator->generate('admin_registration_reject', ['licence' => $licence->getId()]),
                ButtonDto::MODAL_CONTENT,
            );
            $dropdown->addMenuItem(
                'Supprimer l\'inscription',
                'lucide:delete',
                $this->urlGenerator->generate('admin_delete_licence', ['licence' => $licence->getId()]),
                ButtonDto::MODAL_CONTENT,
            );
        }
        
        return $dropdown;
    }

    public function fromBikeRide(BikeRide $bikeRide): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->title = $bikeRide->__toString();
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $dropdown->addMenuItem(
                'Modifier',
                'lucide:pencil',
                $this->urlGenerator->generate('admin_bike_ride_edit', ['bikeRide' => $bikeRide->getId()]),
            );
            if ($bikeRide->getStartAt() > new DateTimeImmutable()) {
                $dropdown->addMenuItem(
                    'Annuler',
                    'lucide:delete',
                    $this->urlGenerator->generate('admin_bike_ride_delete', ['bikeRide' => $bikeRide->getId()]),
                    ButtonDto::MODAL_CONTENT,
                );
            }
            $dropdown->addMenuItem(
                'Exporter la séance',
                'lucide:file-down',
                $this->urlGenerator->generate('admin_bike_ride_export', ['bikeRide' => $bikeRide->getId()]),
            );
        }
        if ($this->security->isGranted('SUMMARY_LIST')) {
            $dropdown->addMenuItem(
                'Actualités',
                'lucide:image',
                $this->urlGenerator->generate('admin_summary_list', ['bikeRide' => $bikeRide->getId()]),
            );
        }
        if ($bikeRide->getBikeRideType()->isPublic()) {
            $dropdown->addActionItem(
                'Copier l\'url',
                'lucide:clipboard-copy',
                [
                    sprintf('data-clipboard-url-value=%s', $this->urlGenerator->generate('bike_ride_detail',
                        ['bikeRide' => $bikeRide->getId(), 'slug' => $bikeRide->getTitle()],
                        UrlGeneratorInterface::ABSOLUTE_URL)
                    ),
                    'data-controller=clipboard'
                ]
            );
        }
                                                         
        return $dropdown;
    }

        public function fromBikeRideType(BikeRideType $bikeRideType): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->addMenuItem(
            'Modifier',
            'lucide:pencil',
            $this->urlGenerator->generate('admin_bike_ride_type_edit', ['bikeRideType' => $bikeRideType->getId()]),
        );

        return $dropdown;
    }

    public function fromSettings(string $sectionName, array $routes = [], array $actions = []): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->trigger = 'lucide:sliders-horizontal';
        $dropdown->position = 'relative';
        $this->addParameters($dropdown, $this->parameterRepository->findByParameterGroupName($sectionName));
        $this->addRoutes($dropdown, $routes);
        $this->addMessages($dropdown, $this->messageService->getMessagesBySectionName($sectionName));
        $this->addActions($dropdown, $actions);

        return $dropdown;
    }
    
    private function addRoutes(DropdownDto $dropdown, array $routes): void
    {
        foreach($routes as $route) {
            $dropdown->addMenuItem(
                $route['label'],
                'lucide:settings-2',
                $this->urlGenerator->generate($route['name']),
            );
        }
    }
    
    private function addActions(DropdownDto $dropdown, array $actions): void
    {
        foreach($actions as $action) {
            $dropdown->addMenuItem(
                $action['label'],
                $action['icon'],
                $this->urlGenerator->generate($action['name'], $action['params']),
            );
        }
    }

    private function addParameters(DropdownDto $dropdown, array $parameters): void
    {
        /** @var Parameter $parameter */
        foreach($parameters as $parameter) {
            $dropdown->addMenuItem(
                $parameter->getLabel(),
                'lucide:settings-2',
                $this->urlGenerator->generate('admin_parameter_edit', ['parameter' => $parameter->getName()]),
                ButtonDto::MODAL_CONTENT,
            );
        }
    }

    private function addMessages(DropdownDto $dropdown, array $messages): void
    {
        foreach($messages as $message) {
            $dropdown->addMenuItem(
                $message['label'],
                'lucide:message-circle',
                $this->urlGenerator->generate('admin_message_edit_content', ['message' => $message['id']]),
                ButtonDto::MODAL_CONTENT,
            );
        }
    }
}