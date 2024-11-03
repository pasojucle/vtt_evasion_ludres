<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin/skill', name: 'admin_skill_')]
class SkillController extends AbstractController
{
    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('skill/admin/list.html.twig');
    }
}
