<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\BikeRide;
use DateTimeImmutable;

class BikeRideDto
{
    public ?int $id = null;

    public ?string $type;

    public ?string $title = null;

    public ?string $shortTitle = null;

    public ?string $content;

    public ?DateTimeImmutable $startAt;

    public ?DateTimeImmutable $endAt;

    public ?int $displayDuration;

    public ?int $closingDuration;

    public ?bool $isWritableAvailability;

    public ?bool $isRegistrable;

    public false|array $unregistrable = false;

    public bool $registrationClosed = false;

    public ?string $rangeAge = null;

    public ?string $displayClass;

    public ?string $period;

    public BikeRideTypeDto $bikeRideType;

    public ?SurveyDto $survey = null;

    public ?string $filename = null;

    public string $members = '';

    public bool $display = false;

    public bool $isEditable = false;

    public ?string $minAge = null;

    public bool $isMultiClusters = false;

    public ?array $btnRegistration = null;
}
