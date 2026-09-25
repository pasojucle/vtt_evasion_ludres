<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Cluster;
use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\SkillCategory;

class ClusterSkillAddPayload
{
    public function __construct(
        public Cluster $cluster,
        public ?SkillCategory $category = null,
        public ?Level $level = null,
        public ?Skill $skill = null,
    ) {
    }
}
