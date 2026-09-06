<?php

declare(strict_types=1);

namespace App\Dto\Service;

final readonly class OperationResult
{
    private function __construct(
        public bool $success,
        public ?string $errorMessage = null,
    ) {
    }

    public static function success(): self
    {
        return new self(
            success: true,
        );
    }

    public static function failure(string $errorMessage): self
    {
        return new self(
            success: false, 
            errorMessage: $errorMessage,
        );
    }
}