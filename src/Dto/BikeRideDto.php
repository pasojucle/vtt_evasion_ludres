<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\BikeRide;
use DateTimeImmutable;

class BikeRideDto
{
    public ?int $id;

    public ?string $type;

    public ?string $title = null;

    public ?string $content;

    public ?DateTimeImmutable $startAt;

    public ?DateTimeImmutable $endAt;

    public ?int $displayDuration;

    public ?int $closingDuration;

    public ?bool $isWritableAvailability;

    public ?bool $isRegistrable;

    public ?int $minAge;

    public ?string $displayClass;

    public ?string $btnLabel;

    public ?string $period;

    public BikeRideTypeDto $bikeRideType;

    public ?SurveyDto $survey;

    public ?string $filename = null;

    public string $members = '';

    public bool $display = false;
}
