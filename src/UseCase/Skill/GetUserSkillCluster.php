<?php

declare(strict_types=1);

namespace App\UseCase\Skill;

use App\Entity\Cluster;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Session;
use App\Entity\Skill;
use App\Repository\MemberSkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class GetUserSkillCluster
{
    public function __construct(
        private readonly MemberSkillRepository $memberSkillRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(Cluster $cluster, Skill $skill): array
    {
        $memberSkills = $this->getUserSkillByUser($cluster, $skill);

        $clusterMemberkill = [];
        /** @var Session $session */
        foreach ($cluster->getSessions() as $session) {
            $member = $session->getUser();
            if ($member instanceof Member && $session->isPresent() && Level::TYPE_SCHOOL_MEMBER === $member->getLevel()->getType()) {
                $clusterMemberkill[] = (array_key_exists($member->getId(), $memberSkills))
                    ? $memberSkills[$member->getId()]
                    : $this->getNewUserSkill($member, $skill);
            }
        }

        return ['memberSkills' => new ArrayCollection($clusterMemberkill)];
    }

    private function getUserSkillByUser(Cluster $cluster, Skill $skill): array
    {
        $memberSkills = [];
        /** @var MemberSkill $memberSkill */
        foreach ($this->memberSkillRepository->findByClusterAndSkill($cluster, $skill) as $memberSkill) {
            $memberSkills[$memberSkill->getMember()->getId()] = $memberSkill;
        }

        return $memberSkills;
    }

    private function getNewUserSkill(Member $member, Skill $skill): MemberSkill
    {
        $memberSkill = (new MemberSkill())->setMember($member)->setSkill($skill);
        $this->entityManager->persist($memberSkill);

        return $memberSkill;
    }
}
