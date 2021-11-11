<?php

namespace App\ViewModel;

use App\Entity\Address;
use ReflectionClass;
use App\Entity\Cluster;
use App\Service\LicenceService;

class AddressViewModel extends AbstractViewModel
{
    public ?int $id;
    public ?string $street;
    public ?int $postalCode;
    public ?string $town;

    public static function fromAddress(Address $address, array $services)
    {
        $addressViewModel = new self();
        $addressViewModel->id = $address->getId();
        $addressViewModel->street = $address->getStreet();
        $addressViewModel->postalCode = $address->getPostalCode();
        $addressViewModel->town = $address->getTown();

        return $addressViewModel;
    }
}