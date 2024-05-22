<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\AddressDto;
use App\Entity\Address;

class AddressDtoTransformer
{
    public function fromEntity(?Address $address, ?array $histories = null): AddressDto
    {
        $addressDto = new AddressDto();
        if ($address) {
            $addressDto->id = $address->getId();
            $addressDto->street = $address->getStreet();
            $addressDto->postalCode = $address->getPostalCode();
            $addressDto->town = $address->getCommune()?->getName() ?? $address->getTown();

            if ($histories) {
                $this->getDecoratedChanges($histories, $addressDto);
            }
        }

        
        return $addressDto;
    }

    private function getDecoratedChanges(array $histories, AddressDto &$addressDto): void
    {
        if (array_key_exists('Address', $histories) && array_key_exists($addressDto->id, $histories['Address'])) {
            $properties = array_keys($histories['Address'][$addressDto->id]->getValue());
            foreach ($properties as $property) {
                if ('commune' === $property) {
                    $property = 'town';
                }
                $addressDto->$property = sprintf('<ins style="background-color:#ccffcc">%s</ins>', $addressDto->$property);
            }
        }
    }
}
