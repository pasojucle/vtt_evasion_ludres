<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserSkill;
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
        /** @var User $user */
        $user = $this->getUser();

        /** @var UserSkill $userSkill */
        foreach ($user->getUserSkills() as $userSkill) {
            $logService->writeFromEntity($userSkill, $user);
        }

        return $this->render('user_skill/list.html.twig', [
            'user' => $user,
        ]);
    }
}
