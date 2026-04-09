<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\LicenceAgreementRepository;
use App\Service\LicenceAgreementService;
use App\Service\SessionService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DropdownDtoTransformer
{
    public function __construct(
        private SessionService $sessionService,
        private LicenceAgreementRepository $licenceAgreementRepository,
        private LicenceAgreementService $licenceAgreementService,
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
        if ($this->security->isGranted('USER_LIST') && $level->getType() === Level::TYPE_SCHOOL_MEMBER) {
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
            if ($level->isAccompanyingCertificat()) {
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
}