<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\BikeRideType;
use Doctrine\Common\Collections\Collection;

class BikeRideTypeDto
{
    public ?BikeRideType $entity;

    public ?string $name;

    public ?string $content;

    public ?bool $isSchool;

    public ?bool $isRegistrable;

    public ?bool $useLevels;

    public ?bool $isShowMemberList;

    public bool $isNeedFramers = false;

    public array|string $messages;

    public bool $displayPractice = false;
}
