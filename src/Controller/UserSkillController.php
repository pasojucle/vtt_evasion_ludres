<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Service\LogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSkillController extends AbstractController
{
    #[Route(path: '/mon-compte/mon-carnet-de-progression', name: 'user_skill_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(LogService $logService): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        /** @var MemberSkill $userSkill */
        foreach ($member->getUserSkills() as $userSkill) {
            $logService->writeFromEntity($userSkill, $member);
        }

        return $this->render('user_skill/list.html.twig', [
            'user' => $member,
        ]);
    }
}
