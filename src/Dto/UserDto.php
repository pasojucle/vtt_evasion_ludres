<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;

class UserDto
{
    public ?int $id;

    public ?array $lines;

    public ?LicenceDto $lastLicence;

    public ?LicenceDto $prevLicence = null;

    public ?LicenceDto $seasonLicence;

    public ?IdentityDto $member;

    public ?IdentityDto $kinship;

    public ?IdentityDto $secondKinship;

    public ?string $licenceNumber;

    public ?HealthDto $health;

    public ?LevelDto $level;

    public ?string $boardRole;

    public ?string $mainEmail;

    public array $approvals = [];

    public bool $isBoardMember = false;

    public ?FFCTLicenceDto $ffctLicence = null;

    public ?string $permissions = null;

    public bool $isEndTesting = false;

    public ?int $testingBikeRides = null;

    public bool $mustProvideRegistration = false;
}
