<?php

declare(strict_types=1);

namespace App\Mapper\ClusterSkill;

use App\Dto\View\ClusterSkill\ClusterSkillsSheetView;
use App\Dto\View\ClusterSkill\ClusterSkillView;
use App\Dto\View\EmptyView;
use App\Entity\Cluster;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;

class ClusterSkillsReadMapper
{
    public function __construct(
        private ClusterSkillMemberMapper $clusterSkillMemberMapper,
    ) {
    }

    /**
     *@param Member[] $participants
     */
    public function mapToView(Cluster $cluster, array $participants): ClusterSkillsSheetView
    {
        return new ClusterSkillsSheetView(
            title: 'Compétences',
            description: sprintF('Compétences à évaluer au groupe %s', $cluster->getTitle()),
            action: 'Fermer',
            items: $cluster->getSkills()->map(fn (Skill $skill) =>
                new ClusterSkillView(
                    content: $skill->getContent(),
                    memberSkills: array_map(function (Member $participant) use ($skill) {
                        $memberSkill = $participant->getMemberSkills()->findFirst(
                            fn (int $key, MemberSkill $memberSkill) =>
                            $memberSkill->getSkill() === $skill
                        );

                        return $this->clusterSkillMemberMapper->mapToView($participant, $skill, $memberSkill);
                    }, $participants)
                ))->toArray(),
            empty: new EmptyView(icon: 'lucide:badge-check', message: 'Aucune évaluation')
        );
    }
}
