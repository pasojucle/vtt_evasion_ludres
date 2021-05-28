<?php

namespace App\DataTransferObject;

use App\Entity\Identity;
use App\Entity\User as UserEntity;

class User
{
    private UserEntity $user;
    private ?Identity $memberIdentity;
    private ?Identity $kinshipIdentity;

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
                ? $this->kinshipIdentity->getFirstName().' '.$this->kinshipIdentity->getName()
                : $this->memberIdentity->getFirstName().' '.$this->memberIdentity->getName();
        }
        return '';
    }

    public function getFullNameChildren()
    {
        if ($this->kinshipIdentity && $this->memberIdentity) {
            return $this->memberIdentity->getFirstName().' '.$this->memberIdentity->getName();
        }
        return '';
    }

    public function getBithDate(): ?string
    {
        if ($this->memberIdentity) {
            $bithDate = ($this->kinshipIdentity)
                ? $this->kinshipIdentity->getBirthDate()
                : $this->memberIdentity->getBirthDate();
            return ($bithDate) ? $bithDate->format('d/m/Y') : null;
        }
        return '';
    }

    public function getBithDateChildren(): ?string
    {
        if ($this->kinshipIdentity && $this->memberIdentity) {
            $bithDate = $this->memberIdentity->getBirthDate();
            return ($bithDate) ? $bithDate->format('d/m/Y') : null;
        }
        return '';
    }
}