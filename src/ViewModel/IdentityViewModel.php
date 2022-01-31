<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Address;
use App\Entity\Identity;

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

    public static function fromIdentity(Identity $identity, array $services, ?Address $address = null)
    {
        $bithDate = $identity->getBirthDate();
        if (null === $address) {
            $address = $identity->getAddress();
        }

        $identityView = new self();
        $identityView->entity = $identity;
        $identityView->name = $identity->getName();
        $identityView->firstName = $identity->getFirstName();
        $identityView->fullName = $identity->getName().' '.$identity->getFirstName();
        $identityView->birthDate = ($bithDate) ? $bithDate->format('d/m/Y') : null;
        $identityView->birthPlace = $identity->getBirthPlace().' ('.$identity->getBirthDepartment().')';
        $identityView->address = (null !== $address) ? AddressViewModel::fromAddress($address, $services) : null;
        $identityView->email = $identity->getEmail();
        $identityView->phone = implode(' - ', array_filter([$identity->getMobile(), $identity->getPhone()]));
        $identityView->picture = $identity->getPicture();
        $identityView->type = (null !== $identity->getKinShip()) ? Identity::KINSHIPS[$identity->getKinShip()] : null;

        return $identityView;
    }
}
