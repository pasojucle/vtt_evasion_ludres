<?php

namespace App\DataTransferObject;

use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User as UserEntity;
use Symfony\Component\Form\DataTransformerInterface;

class User {

    public function __construct(UserEntity $user)
    {
        $this->user = $user;
        $this->memberIdentity = null;
        $this->kinshipIdentity = null;
        $this->setIdentities();
    }

    public function setIdentities(): self
    {
        $identities = $this->user->getIdentities();
        if (1 < $identities->count()) {
            foreach ($identities as $identity) {
                if (null === $identity->getKinship()) {
                    $this->memberIdentity = $identity;

                } else {
                    $this->kinshipIdentity = $identity;
                }
            }
        } else {
            $this->memberIdentity = $identities->first();
            $this->kinshipIdentity = null;
        }

        return $this;
    }

    public function getMemberIdentity(): ?Identity
    {
        return $this->memberIdentity;
    }

    public function getKinshipIdentity(): ?Identity
    {
        return $this->kinshipIdentity;
    }

    public function getFullName(): string
    {
        if ($this->memberIdentity) {
            return ($this->kinshipIdentity)
                ? $this->kinshipIdentity->getName().' '.$this->kinshipIdentity->getFirstName()
                : $this->memberIdentity->getName().' '.$this->memberIdentity->getFirstName();
        }
        return '';
    }

    public function getFullNameChildren()
    {
        if ($this->kinshipIdentity && $this->memberIdentity) {
            return $this->memberIdentity->getName().' '.$this->memberIdentity->getFirstName();
        }
        return '';
    }

    public function getBirthDate(): ?string
    {
        if ($this->memberIdentity) {
            $bithDate = ($this->kinshipIdentity)
                ? $this->kinshipIdentity->getBirthDate()
                : $this->memberIdentity->getBirthDate();
            return ($bithDate) ? $bithDate->format('d/m/Y') : null;
        }
        return '';
    }

    public function getBirthDateChildren(): ?string
    {
        if ($this->kinshipIdentity && $this->memberIdentity) {
            $bithDate = $this->memberIdentity->getBirthDate();
            return ($bithDate) ? $bithDate->format('d/m/Y') : null;
        }
        return '';
    }

    public function getCoverage(string $season): ?int
    {
        $seasonLicence = $this->user->getSeasonLicence($season);
        return (null !== $seasonLicence) ? $seasonLicence->getCoverage() : null;
    }

    public function getSeasonLicence(string $season): ?Licence
    {
        $seasonLicence = [];
        $licence = $this->user->getSeasonLicence($season);
        if (null !== $licence) [
            'isTesting' => $licence->isTesting(),
        ];
        return $this->user->getSeasonLicence($season);
    }

    public function getKinShip(): array
    {
        $kinShip = [];
        if ($this->kinshipIdentity) {
            $kinShip = [
                'fullName' => $this->kinshipIdentity->getName().' '.$this->kinshipIdentity->getFirstName(),
                'type' => Identity::KINSHIPS[$this->kinshipIdentity->getKinShip()] ,
                'address' => $this->kinshipIdentity->getAddress(),
                'email' => $this->kinshipIdentity->getEmail(),
                'phone' => implode(' - ', array_filter([$this->kinshipIdentity->getMobile(), $this->kinshipIdentity->getPhone()])),
            ];
            
        }
        return $kinShip;
    }

    public function getMember(): array
    {
        $member = [];
        if ($this->memberIdentity) {
            $bithDate = $this->memberIdentity->getBirthDate();
            $member = [
                'fullName' => $this->memberIdentity->getName().' '.$this->memberIdentity->getFirstName(),
                'birthDateAndPlace' => ($bithDate) ? $bithDate->format('d/m/Y').' à '.$this->memberIdentity->getBirthPlace() : null,
                'address' => $this->memberIdentity->getAddress(),
                'email' => $this->memberIdentity->getEmail(),
                'phone' => implode(' - ', array_filter([$this->memberIdentity->getMobile(), $this->memberIdentity->getPhone()])),
            ];
            
        }
        return $member;
    }
}