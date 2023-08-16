<?php

declare(strict_types=1);

namespace App\Dto;

class ContentDto
{
    public ?int $id = null;

    public ?string $title = null;

    public ?string $route = null;

    public ?string $content = null;

    public ?bool $isFlash = false;

    public ?string $fileName = null;

    public ?string $fileTag = null;

    public ?float $fileRatio = null;

    public ?string $buttonLabel = null;

    public ?string $url = null;

    public ?string $contentStyleMd = null;
}
