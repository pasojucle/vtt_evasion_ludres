<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;

class ApiUserDto
{
    public ?int $id;

    public string $fullName = '';

    public array $seasons = [];

    public array $member = [];

    public array $level = [];

    public array $permissions = [];

    public string $btnShow;

    public string $testingBikeRides = '';

    public string $boardMember = '';

    public array $actions;

    public bool $isBoardMember = false;


    public ?array $lines;

    public ?LicenceDto $lastLicence;

    public ?LicenceDto $prevLicence = null;

    public ?LicenceDto $seasonLicence;

   

    public ?IdentityDto $kinship;

    public ?IdentityDto $secondKinship;

    public ?string $licenceNumber;

    public ?HealthDto $health;


    public ?string $boardRole;

    public ?string $mainEmail;

    public ?string $mainFullName;

    public array $approvals = [];

    

    public ?FFCTLicenceDto $ffctLicence = null;


    public bool $isEndTesting = false;

    

    public bool $mustProvideRegistration = false;

    public bool $hasAlreadyBeenRegistered = false;
}
