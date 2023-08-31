<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Address;

class AddressDto
{
    public ?string $street;

    public ?string $postalCode;

    public ?string $town;

    public ?int $id = null;

    public function toString(): string
    {
        return $this->street . ', ' . $this->postalCode . ' ' . $this->town;
    }
}
