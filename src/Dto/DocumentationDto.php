<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Documentation;

class DocumentationDto
{
    public int $id;

    public ?string $name = null;

    public ?string $filename = null;

    public ?string $source = null;

    public ?string $mimeType = null;

    public ?string $link = null;

    public bool $novelty = false;
}
