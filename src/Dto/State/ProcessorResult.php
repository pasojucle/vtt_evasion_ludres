<?php

declare(strict_types=1);

namespace App\Dto\State;

readonly class ProcessorResult
{
    /**
     * @param array<string, mixed> $routeParams
     */
    public function __construct(
        public bool $success,
        public string $messageKey,
        public ?string $targetRoute = null,
        public array $routeParams = [],
        public string $flashType = 'success',
    ) {
    }
}
