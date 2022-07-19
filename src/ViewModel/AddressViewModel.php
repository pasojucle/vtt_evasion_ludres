<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Address;

class AddressViewModel extends AbstractViewModel
{
    public ?string $street;

    public ?string $postalCode;

    public ?string $town;

    public ?Address $entity;

    public static function fromAddress(Address $address, ServicesPresenter $services)
    {
        $addressViewModel = new self();
        $addressViewModel->entity = $address;
        $addressViewModel->street = $address->getStreet();
        $addressViewModel->postalCode = $address->getPostalCode();
        $addressViewModel->town = $address->getTown();

        return $addressViewModel;
    }

    public function toString(): string
    {
        return $this->street . ', ' . $this->postalCode . ' ' . $this->town;
    }
}
