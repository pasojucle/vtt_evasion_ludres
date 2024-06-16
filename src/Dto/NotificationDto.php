<?php

declare(strict_types=1);

namespace App\Dto;

class NotificationDto
{
    public ?string $index;

    public ?string $title;

    public ?string $content;

    public ?string $url = null;

    public ?string $labelButton;
}
