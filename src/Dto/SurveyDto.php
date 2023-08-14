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
}
