<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Identity;
use DateTime;

class IdentityViewModel extends AbstractViewModel
{
    public ?string $name;

    public ?string $firstName;

    public ?string $fullName;

    public ?string $birthDate;

    public ?string $birthPlace;

    public ?Identity $entity;

    public ?AddressViewModel $address;

    public ?string $email;

    public ?string $phone;

    public ?string $picture;

    public ?string $type;

    public ?int $age;

    private ServicesPresenter $services;

    public static function fromIdentity(Identity $identity, ServicesPresenter $services, ?IdentityViewModel $member = null)
    {
        $bithDate = $identity->getBirthDate();

        $identityView = new self();
        $identityView->entity = $identity;
        $identityView->services = $services;
        $identityView->name = $identity->getName();
        $identityView->firstName = $identity->getFirstName();
        $identityView->fullName = $identity->getName().' '.$identity->getFirstName();
        $identityView->birthDate = ($bithDate) ? $bithDate->format('d/m/Y') : null;
        $identityView->birthPlace = $identity->getBirthPlace().' ('.$identity->getBirthDepartment().')';
        $identityView->address = $identityView->getAddress($member);
        $identityView->email = $identity->getEmail();
        $identityView->phone = implode(' - ', array_filter([$identity->getMobile(), $identity->getPhone()]));
        $identityView->picture = $identityView->getPicture();
        $identityView->type = (null !== $identity->getKinShip()) ? Identity::KINSHIPS[$identity->getKinShip()] : null;

        $identityView->age = $identityView->getAge();

        return $identityView;
    }

    private function getAge(): ? int
    {
        if (null !== $this->birthDate) {
            $today = new DateTime();
            $birthDate = DateTime::createFromFormat('d/m/Y', $this->birthDate);
            $age = $today->diff($birthDate);

            return (int) $age->format('%y');
        }

        return null;
    }

    private function getAddress(?IdentityViewModel $member): ?AddressViewModel
    {
        $address = $this->entity->getAddress();
        if (Identity::TYPE_KINSHIP === $this->entity->getType() && (null === $address || $address->isEmpty())) {
            $address = $member->address;
        }

        if (null !== $address && !$address instanceof AddressViewModel) {
            $address = AddressViewModel::fromAddress($address, $this->services);
        }

        return  $address;
    }

    private function getPicture(): ?string
    {

        return (null !== $this->entity->getPicture()) ? $this->services->uploadsDirectory.$this->entity->getPicture() : null;
    }
}
