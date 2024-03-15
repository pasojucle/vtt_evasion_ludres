<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Licence;

class LicenceDto
{
    public ?int $id;

    public ?string $createdAt;
    public ?string $createdAtLong;

    public ?int $season = null;

    public ?string $shortSeason;
    public ?string $fullSeason;

    public ?bool $isFinal = false;

    public ?int $coverage;

    public ?string $coverageStr;

    public ?bool $hasFamilyMember;

    public ?int $category = Licence::CATEGORY_ADULT;

    public ?string $statusClass;

    public ?int $status = Licence::STATUS_NEW;

    public ?string $statusStr;

    public ?bool $lock;

    public bool $currentSeasonForm = false;

    public string $isVae;

    public bool $toValidate = false;

    public bool $isSeasonLicence = false;

    public array $amount = [];

    public string $registrationTitle = '';

    public string $licenceSwornCertifications = '';

    public bool $isActive = false;

    public string $additionalFamilyMember = '';
}
