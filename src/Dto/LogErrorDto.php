<?php

declare(strict_types=1);

namespace App\Dto;

class LogErrorDto
{
    public ?int $id;

    public ?string $createdAt;

    public ?int $statusCode;

    public ?string $errorMessage;

    public ?string $message;

    public ?string $userAgent;

    public ?string $route;

    public ?string $url;

    public ?UserDto $user;

    public ?string $fileName;

    public ?int $line;
}
