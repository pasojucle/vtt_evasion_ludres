<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\BikeRide;
use App\Entity\Member;
use Symfony\Contracts\EventDispatcher\Event;

class ClusterCompleteToggleEvent extends Event
{
    /**
     * @param Member[] $absentParticipants
     */
    public function __construct(
        public readonly BikeRide $bikeRide,
        public readonly array $absentParticipants,
    ) {
    }
}
