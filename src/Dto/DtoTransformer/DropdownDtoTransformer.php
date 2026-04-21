<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\RouteDto;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Parameter;
use App\Entity\Product;
use App\Entity\Session;
use App\Entity\Survey;
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
    ) {
    }
    public function fromUser(User $user): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->setUrlGenerator($this->urlGenerator);
        $dropdown->title = $user->getIdentity()->getFullName();
        $level = $user->getLevel();
        if ($this->security->isGranted('USER_LIST') && $level?->getType() === Level::TYPE_SCHOOL_MEMBER) {
            $dropdown->addMenuItem(
                'Compétences',
                new RouteDto('admin_member_skill_edit', ['member' => $user->getId()]),
                'lucide:graduation-cap',
            );
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $dropdown->addMenuItem(
                'Participation',
                new RouteDto('admin_user_participation', ['user' => $user->getId()]),
                'lucide:chart-line',
            );
            $dropdown->addMenuItem(
                'Attestation d\'inscription CE',
                new RouteDto('admin_user_certificate', ['member' => $user->getId()]),
                'lucide:file-user',
            );
            if ($level?->isAccompanyingCertificat()) {
                $dropdown->addMenuItem(
                    'Attestation adulte accompagnateur',
                    new RouteDto('admin_user_accompanying_certificate', ['member' => $user->getId()]),
                    'lucide:file-terminal',
                );
            }
            if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
                $dropdown->addMenuItem(
                    'Se connecter en tant que',
                    new RouteDto('home', ['_switch_user' => $user->getLicenceNumber()]),
                    'lucide:arrow-left-right',
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
                    new RouteDto('admin_bike_ride_switch_cluster', ['session' => $session->getId()]),
                    'lucide:refresh-cw',
                );
            }
            $dropdown->addMenuItem(
                'Supprimer',
                new RouteDto('admin_session_delete', ['session' => $session->getId()]),
                'lucide:delete',
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
                new RouteDto('admin_registration_reject', ['licence' => $licence->getId()]),
                'lucide:message-circle-warning',
                ButtonDto::MODAL_CONTENT,
            );
            $dropdown->addMenuItem(
                'Supprimer l\'inscription',
                new RouteDto('admin_delete_licence', ['licence' => $licence->getId()]),
                'lucide:delete',
                ButtonDto::MODAL_CONTENT,
            );
        }
        
        return $dropdown;
    }

    public function fromBikeRide(BikeRide $bikeRide): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->setUrlGenerator($this->urlGenerator);
        $dropdown->title = $bikeRide->__toString();
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $dropdown->addMenuItem(
                'Modifier',
                new RouteDto('admin_bike_ride_edit', ['bikeRide' => $bikeRide->getId()]),
                'lucide:pencil',
            );
            if ($bikeRide->getStartAt() > new DateTimeImmutable()) {
                $dropdown->addMenuItem(
                    'Annuler',
                    new RouteDto('admin_bike_ride_delete', ['bikeRide' => $bikeRide->getId()]),
                    'lucide:delete',
                    ButtonDto::MODAL_CONTENT,
                );
            }
            $dropdown->addMenuItem(
                'Exporter la séance',
                new RouteDto('admin_bike_ride_export', ['bikeRide' => $bikeRide->getId()]),
                'lucide:file-down',
            );
        }
        if ($this->security->isGranted('SUMMARY_LIST')) {
            $dropdown->addMenuItem(
                'Actualités',
                new RouteDto('admin_summary_list', ['bikeRide' => $bikeRide->getId()]),
                'lucide:image',
            );
        }
        if ($bikeRide->getBikeRideType()->isPublic()) {
            $dropdown->addActionItem(
                'Copier l\'url',
                'lucide:clipboard-copy',
                [
                    'data-clipboard-url-value' => $this->urlGenerator->generate(
                        'bike_ride_detail',
                        ['bikeRide' => $bikeRide->getId(), 'slug' => $bikeRide->getTitle()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'data-controller' => 'clipboard',
                    'data-action' => 'click->dropdown#close'
                ]
            );
        }
                                                         
        return $dropdown;
    }

    public function fromBikeRideType(BikeRideType $bikeRideType): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->setUrlGenerator($this->urlGenerator);
        $dropdown->addMenuItem(
            'Modifier',
            new RouteDto('admin_bike_ride_type_edit', ['bikeRideType' => $bikeRideType->getId()]),
            'lucide:pencil',
        );

        return $dropdown;
    }

    public function fromProduct(Product $product): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->setUrlGenerator($this->urlGenerator);
        if ($product->isDisabled()) {
            $dropdown->addMenuItem(
                'Activer',
                new RouteDto('admin_product_disable', ['product' => $product->getId()]),
                'lucide:toggle-left',
                ButtonDto::MODAL_CONTENT,
            );
        } else {
            $dropdown->addMenuItem(
                'Désactiver',
                new RouteDto('admin_product_disable', ['product' => $product->getId()]),
                'lucide:toggle-right',
                ButtonDto::MODAL_CONTENT,
            );
        }

        $dropdown->addMenuItem(
            'Supprimer',
            new RouteDto('admin_product_delete', ['product' => $product->getId()]),
            'lucide:delete',
            ButtonDto::MODAL_CONTENT,
        );

        return $dropdown;
    }

    public function fromSurvey(Survey $survey): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->setUrlGenerator($this->urlGenerator);
        $dropdown->addActionItem(
            'Copier les emails de la séléction',
            'lucide:clipboard-type',
            [
                'data-email-to-clipboard-url-value' => $this->urlGenerator->generate('admin_survey_email_to_clipboard'),
                'data-controller' => 'email-to-clipboard',
                'data-action' => 'click->email-to-clipboard#emailToClipboard click->dropdown#close',
            ]
        );

        return $dropdown;
    }

    public function fromSurveyForList(Survey $survey): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->setUrlGenerator($this->urlGenerator);
        $dropdown->title = $survey->getTitle();

        $dropdown->addActionItem(
            'Copier l\'url',
            'lucide:clipboard-copy',
            [
                
                'data-clipboard-url-value' => $this->urlGenerator->generate('survey', 
                    ['survey' => $survey->getId()], UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'data-controller' => 'clipboard',
                'data-action' => 'click->dropdown#close'
            ]
        );
        $dropdown->addMenuItem(
            'Exporter',
            new RouteDto('admin_survey_export', ['survey' => $survey->getId()]),
            'lucide:file-down',
        );
        $dropdown->addMenuItem(
            'Dupliquer',
            new RouteDto('admin_survey_copy', ['survey' => $survey->getId()]),
            'lucide:copy-plus',
        );
        if (!$survey->isDisabled()) {
            $dropdown->addMenuItem(
                'Modifier',
                new RouteDto('admin_survey_edit', ['survey' => $survey->getId()]),
                'lucide:pencil',
            );
            $dropdown->addMenuItem(
                'Cloturer',
                new RouteDto('admin_survey_disable', ['survey' => $survey->getId()]),
                'lucide:toggle-left',
                ButtonDto::MODAL_CONTENT,
            );
        }
        $dropdown->addMenuItem(
            'Supprimer',
            new RouteDto('admin_survey_delete', ['survey' => $survey->getId()]),
            'lucide:delete',
            ButtonDto::MODAL_CONTENT,
        );

        return $dropdown;
    }

    public function fromSettings(string $sectionName): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->setUrlGenerator($this->urlGenerator);
        $dropdown->trigger = 'lucide:sliders-horizontal';
        $dropdown->position = 'relative';
        $this->addParameters($dropdown, $this->parameterRepository->findByParameterGroupName($sectionName));
        $this->addMessages($dropdown, $this->messageService->getMessagesBySectionName($sectionName));

        return $dropdown;
    }

    public function fromTools(): DropdownDto
    {
        $dropdown = new DropdownDto();
        $dropdown->setUrlGenerator($this->urlGenerator);
        $dropdown->position = 'relative';

        return $dropdown;
    }

    private function addParameters(DropdownDto $dropdown, array $parameters): void
    {
        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            $dropdown->addSectionItem(
                $parameter->getLabel(),
                new RouteDto('admin_parameter_edit', ['name' => $parameter->getName()]),
                'lucide:settings-2',
                ButtonDto::MODAL_CONTENT,
            );
        }
    }

    private function addMessages(DropdownDto $dropdown, array $messages): void
    {
        foreach ($messages as $message) {
            $dropdown->addSectionItem(
                $message['label'],
                new RouteDto('admin_message_edit_content', ['message' => $message['id']]),
                'lucide:message-circle',
                ButtonDto::MODAL_CONTENT,
            );
        }
    }
}
