<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Level;

class LevelDto
{
    public ?Level $entity;

    public ?string $title;

    public ?int $type;

    public ?array $colors;

    public ?bool $accompanyingCertificat;
}
