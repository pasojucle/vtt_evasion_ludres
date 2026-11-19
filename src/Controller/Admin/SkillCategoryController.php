<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Entity\SkillCategory;
use App\Form\Admin\SkillCategoryType;
use App\State\SkillCategory\Processor\SkillCategoryDeleteProcessor;
use App\State\SkillCategory\Processor\SkillCategoryRestoreProcessor;
use App\State\SkillCategory\Processor\SkillCategoryUpdateProcessor;
use App\State\SkillCategory\Provider\SkillCategoryDeleteProvider;
use App\State\SkillCategory\Provider\SkillCategoryListProvider;
use App\State\SkillCategory\Provider\SkillCategoryRestoreProvider;
use App\State\SkillCategory\Provider\SkillCategoryUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/competences/category', name: 'admin_skill_category_')]
class SkillCategoryController extends AbstractController
{
    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        SkillCategoryListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[IsGranted('SKILL_ADD')]
    public function add(
        Request $request,
        SkillCategoryUpdateProvider $provider,
        SkillCategoryUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new SkillCategory(),
            $provider,
            $processor,
            SkillCategoryType::class
        );
    }

    #[Route(path: '/edit/{skillCategory}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_EDIT', 'skillCategory')]
    public function edit(
        Request $request,
        SkillCategoryUpdateProvider $provider,
        SkillCategoryUpdateProcessor $processor,
        SkillCategory $skillCategory,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $skillCategory,
            $provider,
            $processor,
            SkillCategoryType::class
        );
    }

    #[Route(path: '/delete/{skillCategory}', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[IsGranted('SKILL_EDIT', 'skillCategory')]
    public function delete(
        Request $request,
        SkillCategory $skillCategory,
        SkillCategoryDeleteProvider $provider,
        SkillCategoryDeleteProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $skillCategory,
            $provider,
            $processor
        );
    }

    #[Route('/restaure/{skillCategory}', name: 'restore', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function restore(
        Request $request,
        SkillCategoryRestoreProvider $provider,
        SkillCategoryRestoreProcessor $processor,
        SkillCategory $category,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $category,
            $provider,
            $processor
        );
    }
}
