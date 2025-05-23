<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Survey;

class SurveyDto
{
    public ?int $id;

    public ?Survey $entity;

    public string $title = '';

    public string $content = '';

    public array $issues = [];

    public array $responses = [];

    public ?string $members = null;

    public bool  $isEditable = true;

    public ?string $bikeRide = null;
}
