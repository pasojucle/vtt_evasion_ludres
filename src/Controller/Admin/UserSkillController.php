<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Member;
use App\UseCase\Skill\GetUsersSkills;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/competences', name: 'admin_user_skill_')]
class UserSkillController extends AbstractController
{
    #[Route(path: '/edit/{user}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(UserDtoTransformer $userDtoTransformer, Member $member): Response
    {
        return $this->render('user_skill/admin/list.html.twig', [
            'user' => $userDtoTransformer->getHeaderFromEntity($member),
        ]);
    }

    #[Route('/export', name: 'export', methods: ['GET'])]
    #[IsGranted('MEMBER_SHARE')]
    public function export(
        GetUsersSkills $getUsersSkills,
    ): Response {
        return $getUsersSkills->export();
    }
}
