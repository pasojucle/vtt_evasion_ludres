<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Documentation;

class DocumentationDto
{
    public ?Documentation $entity;

    public ?string $name;

    public ?string $filename;

    public ?string $source;

    public ?string $mimeType;
}
