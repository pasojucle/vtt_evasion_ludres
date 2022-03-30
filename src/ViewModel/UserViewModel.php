<?php

declare(strict_types=1);

namespace App\ViewModel;

use DateTime;
use App\Entity\User;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Identity;
use App\ViewModel\ServicesPresenter;

class UserViewModel extends AbstractViewModel
{
    public ?User $entity;

    public ?array $lines;

    public ?LicenceViewModel $lastLicence;

    public ?LicenceViewModel $seasonLicence;

    public ?IdentityViewModel $member;

    public ?IdentityViewModel $kinship;

    public ?IdentityViewModel $secondKinship;

    public ?string $licenceNumber;

    public ?bool $isNewMember;

    public ?HealthViewModel $health;

    private ?int $currentSeason;

    private ServicesPresenter $services;

    public static function fromUser(User $user, ServicesPresenter $services)
    {
        $userView = new self();
        $userView->entity = $user;
        $userView->isNewMember = $userView->isNewMember();
        $userView->services = $services;
        $userView->licenceNumber = $user->getLicenceNumber();
        $userView->setIdentities();
        $userView->currentSeason = $services->currentSeason;
        $userView->seasonsStatus = $services->seasonsStatus;
        $userView->membershipFeeAmountRepository = $services->membershipFeeAmountRepository;
        $userView->translator = $services->translator;
        $userView->lastLicence = $userView->getLastLicence();
        $userView->seasonLicence = $userView->getSeasonLicence();
        $userView->health = HealthViewModel::fromHealth($user->getHealth(), $services);

        return $userView;
    }

    public function setIdentities(): self
    {
        $identities = $this->entity->getIdentities();
        if (!$identities->isEmpty()) {
            foreach ($identities as $identity) {
                if (Identity::TYPE_MEMBER === $identity->getType()) {
                    $this->member = $this->getMember($identity);
                }
                if (Identity::TYPE_KINSHIP === $identity->getType()) {
                    $this->kinship = $this->getKinship($identity);;
                }
                if (Identity::TYPE_SECOND_CONTACT=== $identity->getType()) {
                    $this->secondKinship = $this->getSecondKinShip($identity);
                }
            }
        } else {
            $this->member = null;
            $this->kinship = null;
        }

        return $this;
    }

    public function getFullName(): string
    {
        if (null !== $this->member) {
            return ($this->kinship)
                ? $this->kinship->name.' '.$this->kinship->firstName
                : $this->member->name.' '.$this->member->firstName;
        }

        return '';
    }

    public function getFullNameChildren()
    {
        if ($this->kinship && $this->member) {
            return $this->member->name.' '.$this->member->firstName;
        }

        return '';
    }

    public function getBirthDate(): ?string
    {
        if ($this->member) {
            $birthDate = ($this->kinship)
                ? $this->kinship->birthDate
                : $this->member->birthDate;

            return ($birthDate) ? $birthDate : null;
        }

        return '';
    }

    public function getBirthDateChildren(): ?string
    {
        if ($this->kinship && $this->member) {
            $birthDate = $this->member->birthDate;

            return ($birthDate) ? $birthDate : null;
        }

        return '';
    }

    public function getCoverage(): ?int
    {
        $seasonLicence = $this->entity->getSeasonLicence($this->currentSeason);

        return (null !== $seasonLicence) ? $seasonLicence->getCoverage() : null;
    }

    public function isNewMember(): bool
    {
        return 2 > $this->entity->getLicences()->count();
    }

    public function isMedicalCertificateRequired(): string
    {
        $message = '';
        $licence = $this->entity->getSeasonLicence($this->currentSeason);

        if (null !== $licence && $licence->isMedicalCertificateRequired()) {
            $message = 'Vous devez joindre un certificat médical daté DE MOINS DE 12 MOIS de non contre-indication à la pratique du VTT';
        }

        return $message;
    }

    public function getApprovals()
    {
        $approvals = [];
        if (!$this->entity->getApprovals()->isEmpty()) {
            foreach ($this->entity->getApprovals() as $approval) {
                $string = ($approval->getValue()) ? 'autorise' : 'n\'autorise pas';
                if (User::APPROVAL_GOING_HOME_ALONE === $approval->getType()) {
                    $approvals['goingHomeAlone'] = [
                        'value' => $approval->getValue(),
                        'string' => $string,
                    ];
                }
                if (User::APPROVAL_RIGHT_TO_THE_IMAGE === $approval->getType()) {
                    $approvals['rightToImage'] = [
                        'value' => $approval->getValue(),
                        'string' => $string,
                    ];
                }
            }
        }

        return $approvals;
    }

    public function getApprovalGoingHome(): ?array
    {
        $approvalGoingHome = null;
        if (!$this->entity->getApprovals()->isEmpty()) {
            foreach ($this->entity->getApprovals() as $approval) {
                if (User::APPROVAL_GOING_HOME_ALONE === $approval->getType()) {
                    $approvalGoingHome = ($approval->getValue())
                        ? [
                            'class' => 'success',
                            'message' => 'Autorisé à rentrer seul',
                        ]
                        : [
                            'class' => 'alert-danger',
                            'message' => 'Pas autorisé à rentrer seul',
                        ];
                }
            }
        }

        return $approvalGoingHome;
    }

    public function getLevel(): ?Level
    {
        return $this->entity->getLevel();
    }

    public function getLicenceNumber(): ?string
    {
        return $this->entity->getLicenceNumber();
    }

    public function getBikeRides(): array
    {
        $bikeRides = [];
        $today = new DateTime();

        $sessions = $this->entity?->getSessions();
        if (!$sessions?->isEmpty()) {
            foreach ($sessions as $session) {
                $session = SessionViewModel::fromSession($session, $this->services);
                if ($today <= $session->bikeRide->startAt->setTime(14, 0, 0)) {
                    $bikeRides[] = [
                        'bikeRide' => $session->bikeRide,
                        'availability' => $session->availability,
                        'sessionId' => $session->entity->getId(),
                    ];
                }
            }
        }

        return $bikeRides;
    }

    public function isMember(): bool
    {
        $type = (null !== $this->entity->getLevel()) ? $this->entity->getLevel()->getType() : null;

        return Level::TYPE_MEMBER === $type;
    }

    public function isFramer(): bool
    {
        $type = (null !== $this->entity->getLevel()) ? $this->entity->getLevel()->getType() : null;

        return Level::TYPE_FRAME === $type;
    }

    public function isEndTesting(): bool
    {
        if (!$this->seasonLicence->isFinal) {
            $count = (null !== $this->entity->getSessions()) ? $this->entity->getSessions()->count() : 0;

            return 2 < $count;
        }

        return false;
    }

    public function testingBikeRides(): ?int
    {
        if (!$this->seasonLicence->isFinal) {
            return (null !== $this->entity->getSessions()) ? $this->entity->getSessions()->count() : 0;
        }

        return null;
    }

    public function getContactEmail(): ?string
    {
        $member = $this->member;
        $licence = $this->getLastLicence();
        if (Licence::CATEGORY_MINOR === $licence->category) {
            $member = $this->kinship;
        }

        return $member->email;
    }

    public function mustProvideRegistration(): bool
    {
        $lastLicence = $this->getLastLicence();

        return 1 === $this->entity->getLicences()->count() && $lastLicence->season === $this->currentSeason && $lastLicence->isFinal && Licence::STATUS_WAITING_VALIDATE === $lastLicence->status;
    }

    private function getSeasonLicence(): LicenceViewModel
    {
        $licence = $this->entity->getSeasonLicence($this->currentSeason);

        return LicenceViewModel::fromLicence($licence, $this->isNewMember, $this->services);
    }

    private function getLastLicence(): LicenceViewModel
    {
        $licence = $this->entity->getLastLicence();

        return LicenceViewModel::fromLicence($licence, $this->isNewMember, $this->services);
    }

    private function getKinship(Identity $identity): ?IdentityViewModel
    {
        if ($identity) {

            return IdentityViewModel::fromIdentity($identity, $this->services, $this->member);
        }

        return null;
    }

    private function getSecondKinShip(Identity $identity): ?IdentityViewModel
    {
        if ($identity) {
            return IdentityViewModel::fromIdentity($identity, $this->services);
        }

        return null;
    }

    private function getMember(Identity $identity): ?IdentityViewModel
    {
        if ($identity) {
            return IdentityViewModel::fromIdentity($identity, $this->services);
        }

        return null;
    }
}
