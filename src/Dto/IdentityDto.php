<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\AddressDto;

class IdentityDto
{
    public ?string $name = null;

    public ?string $firstName = null;

    public ?string $fullName = null;

    public ?string $birthDate;

    public ?string $birthDepartment;

    public ?string $birthPlace;

    public ?string $birthCountry;

    public ?int $id = null;

    public ?AddressDto $address;

    public ?string $email;

    public ?string $phone;

    public ?string $emergencyPhone;

    public ?string $emergencyPhoneAnchor;

    public ?string $emergencyContact;

    public ?string $phonesAnchor;

    public ?string $picture;

    public ?string $type;

    public ?int $age;
}
