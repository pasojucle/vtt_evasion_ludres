<?php

declare(strict_types=1);

namespace App\Dto\Service;

final readonly class ParticipationIndemnityResult
{
    /**
     * @param float $totalAmount
     * @param array<int, float> $amountsBySessionId
     */
    public function __construct(
        public float $totalAmount = 0.0,
        public array $amountsBySessionId = [],
    ) {
    }

    public function getAmountForSession(int $sessionId): float
    {
        return $this->amountsBySessionId[$sessionId] ?? 0.0;
    }

    public function hasIndemnity(): bool
    {
        return $this->totalAmount > 0.0;
    }
}
