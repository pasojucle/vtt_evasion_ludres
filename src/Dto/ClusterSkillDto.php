<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Cluster;
use App\Entity\Skill;

class ClusterSkillDto
{
    public function __construct(
        public Cluster $cluster,
        public Skill $skill
    ) {
    }
}
