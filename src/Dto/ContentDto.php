<?php

declare(strict_types=1);

namespace App\Dto;


class ContentDto
{
    public ?int $id;

    public ?string $title;

    public ?string $route;

    public ?string $content;

    public ?bool $isFlash;

    public ?string $fileName;

    public ?string $fileTag;

    public ?float $fileRatio;

    public ?string $buttonLabel;

    public ?string $url;

    public ?string $contentStyleMd;
}