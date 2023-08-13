<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Licence;
use App\Model\Currency;

class LicenceDto
{
    public ?Licence $entity;

    public ?string $createdAt;

    public ?string $season;

    public ?string $fullSeason;

    public ?bool $isFinal = false;

    public ?int $coverage;

    public ?string $coverageStr;

    public ?bool $hasFamilyMember;

    public ?int $category = Licence::CATEGORY_ADULT;

    public ?string $statusClass;

    public ?int $status;

    public ?string $statusStr;

    public ?string $type;

    public ?bool $lock;

    public bool $currentSeasonForm = false;

    public string $isVae;

    public bool $toValidate = false;

    public bool $isSeasonLicence = false;

    public array $amount = [];

    public string $registrationTitle = '';
}