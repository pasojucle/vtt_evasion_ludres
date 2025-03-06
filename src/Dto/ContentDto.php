<?php

declare(strict_types=1);

namespace App\Dto;

class ContentDto
{
    public ?int $id = null;

    public ?string $title = null;

    public string $route = 'home';

    public string $routeName = 'content.route.home';

    public ?string $content = null;

    public ?string $kind = null;

    public ?string $fileName = null;

    public ?string $fileTag = null;

    public ?float $fileRatio = null;

    public ?string $buttonLabel = null;

    public ?string $url = null;

    public ?string $contentStyleMd = null;
}
