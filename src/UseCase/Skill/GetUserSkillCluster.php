<?php

declare(strick_type=1);

namespace App\UseCase\Skill;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\Cluster;
use App\Entity\UserSkill;
use App\Repository\UserSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class GetUserSkillCluster
{
    public function __construct(
        private readonly UserSkillRepository $userSkillRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        
    }

    public function execute(Cluster $cluster, Skill $skill): array
    {
        $userSkills = $this->getUserSkillByUser($cluster, $skill);

        $clusterUserSkill = [];
        /** @var Session $session */
        foreach($cluster->getSessions() as $session) {
            if ($session->isPresent()) {
                $user = $session->getUser();

                $clusterUserSkill[] = (array_key_exists($user->getId(), $userSkills))
                    ? $userSkills[$user->getId()]
                    : $this->getNewUserSkill($user, $skill); 
            }
        }

        return ['userSkills' => new ArrayCollection($clusterUserSkill)];
    }

    private function getUserSkillByUser(Cluster $cluster, Skill $skill): array
    {
        $userSkills = [];
        /** @var UserSkill $userSkill */
        foreach($this->userSkillRepository->findByClusterAndSkill($cluster, $skill) as $userSkill) {
            $userSkills[$userSkill->getUser()->getId()] = $userSkill;
        }

        return $userSkills;
    }

    private function getNewUserSkill(User $user, Skill $skill): UserSkill
    {
        $userSkill = (new UserSkill())->setUser($user)->setSkill($skill);
        $this->entityManager->persist($userSkill);

        return $userSkill;
    }
}
