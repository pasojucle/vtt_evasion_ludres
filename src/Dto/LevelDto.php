<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Level;

class LevelDto
{
    public ?Level $entity;

    public ?string $title = null;

    public ?int $type = null;

    public ?array $colors = ['background' => '#aaaaaa', 'color' => '#ffffff'];

    public ?bool $accompanyingCertificat = null;
}
