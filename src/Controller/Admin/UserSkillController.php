<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin/competences', name: 'admin_user_skill_')]
class UserSkillController extends AbstractController
{
    #[Route(path: '/edit/{user}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(UserDtoTransformer $userDtoTransformer, User $user): Response
    {
        return $this->render('user_skill/admin/list.html.twig', [
            'user' => $userDtoTransformer->getHeaderFromEntity($user),
        ]);
    }
}
