<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Cluster;
use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Enum\PracticeEnum;
use App\Entity\User;

class SessionCreateAdminPayload
{
    public function __construct(
        public Cluster $cluster,
        public bool $isFramer = false,
        public ?int $season = null,
        public null|string|int $level = null,
        public ?User $user = null,
        public PracticeEnum $practice = PracticeEnum::NONE,
        public BikeTypeEnum $bikeType = BikeTypeEnum::NONE,
        public ?array $surveyResponses = null,
    ) {
    }
}
