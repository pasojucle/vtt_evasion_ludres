<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\AddressDto;
use App\Entity\Identity;

class IdentityDto
{
    public ?string $name = null;

    public ?string $firstName = null;

    public ?string $fullName = null;

    public ?string $birthDate;

    public ?string $birthPlace;

    public ?int $id = null;

    public ?AddressDto $address;

    public ?string $email;

    public ?string $phone;

    public ?string $emergencyPhone;

    public ?string $phonesAnchor;

    public ?string $picture;

    public ?string $type;

    public ?int $age;
}
