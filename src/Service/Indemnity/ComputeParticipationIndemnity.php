<?php

declare(strict_types=1);

namespace App\Service\Indemnity;

use App\Dto\Service\ParticipationIndemnityResult;
use App\Entity\Enum\LevelType;
use App\Entity\Level;
use App\Entity\Session;

final class ComputeParticipationIndemnity
{
    /**
     * @param iterable<Session> $sessions
     * @param Level|null $level
     * @param array<string, float> $indemnityMap
     */
    public function __invoke(
        iterable $sessions,
        ?Level $level,
        array $indemnityMap
    ): ParticipationIndemnityResult {
        if (null === $level || LevelType::FRAME !== $level->getType()) {
            return new ParticipationIndemnityResult();
        }

        $levelId = $level->getId();
        $totalAmount = 0.0;
        $amountsBySessionId = [];

        foreach ($sessions as $session) {
            $bikeRideType = $session->getCluster()?->getBikeRide()?->getBikeRideType();
            
            if (null === $bikeRideType) {
                continue;
            }

            $key = sprintf('%d_%d', $levelId, $bikeRideType->getId());
            $amount = $indemnityMap[$key] ?? 0.0;

            $sessionId = $session->getId();
            if (null !== $sessionId) {
                $amountsBySessionId[$sessionId] = $amount;
            }

            $totalAmount += $amount;
        }

        return new ParticipationIndemnityResult(
            totalAmount: $totalAmount,
            amountsBySessionId: $amountsBySessionId,
        );
    }
}
