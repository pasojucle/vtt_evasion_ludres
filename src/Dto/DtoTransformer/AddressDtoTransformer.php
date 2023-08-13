<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\AddressDto;
use App\Entity\Address;

class AddressDtoTransformer
{
    public function fromEntity(?Address $address, ?array $changes = null): AddressDto
    {
        $addressDto = new AddressDto();
        if ($address) {
            $addressDto->entity = $address;
            $addressDto->street = $address->getStreet();
            $addressDto->postalCode = $address->getPostalCode();
            $addressDto->town = $address->getCommune()?->getName() ?? $address->getTown();

            if ($changes) {
                $this->formatChanges($changes, $addressDto);
            }
        }

        
        return $addressDto;
    }

    private function formatChanges(array $changes, AddressDto &$addressDto): void
    {
        if (array_key_exists('Address', $changes)) {
            foreach($changes['Address']->getValue() as $property) {
                $addressDto->$property = sprintf('<b>%s</b>', $addressDto->$property); 
            }
        }
    }
}