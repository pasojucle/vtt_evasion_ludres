<?php

declare(strict_types=1);

namespace App\UseCase\v2\MemberSkill;

use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;

final class CreateMemberSkill
{
    public function __invoke(
        Member $member,
        Skill $skill
    ): MemberSkill {
        $newEntity = new MemberSkill();
        $newEntity
            ->setMember($member)
            ->setSkill($skill);

        return $newEntity;
    }
}
