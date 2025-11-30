<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;

class LicenceDto
{
    public ?int $id;

    public ?string $createdAt;
    public ?string $createdAtLong;
    public ?string $testingAt;
    public ?string $testingAtLong;
    public ?int $season = null;

    public ?string $shortSeason;
    public ?string $fullSeason;

    public ?bool $isYearly = false;

    public ?int $coverage;

    public ?string $coverageStr;

    public array $options;

    public LicenceCategoryEnum $category = LicenceCategoryEnum::ADULT;

    public array $state;

    public ?bool $lock;

    public bool $currentSeasonForm = false;

    public ?string $isVae;

    public bool $toValidate = false;

    public bool $toRegister = false;

    public bool $isRegistered = false;

    public bool $isSeasonLicence = false;

    public array $amount = [];

    public string $registrationTitle = '';

    public array $licenceAuthorizationConsents = [];

    public array $licenceHealthConsents = [];

    public array $licenceOvewiewConsents = [];

    public bool $isActive = false;

    public ?string $additionalFamilyMember = null;
    public ?array $familyMember = null;

    public array $authorizations = [];
}
