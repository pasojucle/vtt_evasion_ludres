<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Entity\SecondHandCategory;
use App\Form\Admin\SecondHandCategoryType;
use App\State\SecondHandCategory\Processor\SecondHandCategoryDeleteProcessor;
use App\State\SecondHandCategory\Processor\SecondHandCategoryRestoreProcessor;
use App\State\SecondHandCategory\Processor\SecondHandCategoryUpdateProcessor;
use App\State\SecondHandCategory\Provider\SecondHandCategoryDeleteProvider;
use App\State\SecondHandCategory\Provider\SecondHandCategoryListProvider;
use App\State\SecondHandCategory\Provider\SecondHandCategoryRestoreProvider;
use App\State\SecondHandCategory\Provider\SecondHandCategoryUpdateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/occasion-categorie', name: 'admin_second_hand_category_')]
class SecondHandCategoryController extends AbstractController
{
    #[Route('s', name: 'list', methods: ['GET'])]
    #[IsGranted('SECOND_HAND_LIST')]
    public function list(
        Request $request,
        SecondHandCategoryListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_ADD')]
    public function add(
        Request $request,
        SecondHandCategoryUpdateProvider $provider,
        SecondHandCategoryUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new SecondHandCategory(),
            $provider,
            $processor,
            SecondHandCategoryType::class
        );
    }

    #[Route('/{category}', name: 'edit', methods: ['GET', 'POST'], defaults:['category' => null])]
    #[IsGranted('SECOND_HAND_EDIT', 'category')]
    public function edit(
        Request $request,
        SecondHandCategoryUpdateProvider $provider,
        SecondHandCategoryUpdateProcessor $processor,
        SecondHandCategory $category,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $category,
            $provider,
            $processor,
            SecondHandCategoryType::class
        );
    }

    #[Route('supprimer/{category}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_EDIT', 'category')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        SecondHandCategoryDeleteProvider $provider,
        SecondHandCategoryDeleteProcessor $processor,
        SecondHandCategory $category,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $category,
            $provider,
            $processor
        );
    }

    #[Route('/restaure/{category}', name: 'restore', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelRestore(
        Request $request,
        SecondHandCategoryRestoreProvider $provider,
        SecondHandCategoryRestoreProcessor $processor,
        SecondHandCategory $category,
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
