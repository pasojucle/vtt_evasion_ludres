<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSkillController extends AbstractController
{
    #[Route(path: '/mon-compte/mon-carnet-de-progression', name: 'user_skill_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('user_skill/list.html.twig', [
            'user' => $user,
        ]);
    }
}
