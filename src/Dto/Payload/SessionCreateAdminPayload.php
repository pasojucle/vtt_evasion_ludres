<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Cluster;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Member;

class SessionCreateAdminPayload
{
    public function __construct(
        public int $season,
        public Cluster $cluster,
        public ?Member $member = null,
        public ?PracticeEnum $practice = null,
        public ?array $surveyResponses = null,
    ) {
    }
}
