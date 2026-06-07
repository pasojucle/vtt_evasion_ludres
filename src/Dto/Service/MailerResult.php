<?php

declare(strict_types=1);

namespace App\Dto\Service;

class MailerResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?string $errorMessage = null,
    ) {
    }

    public static function success(): self
    {
        return new self(true);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, $errorMessage);
    }
}