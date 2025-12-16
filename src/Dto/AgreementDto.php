<?php

declare(strict_types=1);

namespace App\Dto;

class AgreementDto
{
    public string $id;

    public string $title;

    public array $category;

    public array $membership;

    public bool $enabled;
}
