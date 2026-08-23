<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\SecondHandCategoryFilter;
use App\Entity\SecondHandCategory;
use App\Form\Admin\SecondHandCategoryType;
use App\State\SecondHandCategory\Processor\SecondHandCategoryCreateProcessor;
use App\State\SecondHandCategory\Processor\SecondHandCategoryDeleteProcessor;
use App\State\SecondHandCategory\Processor\SecondHandCategoryRestoreProcessor;
use App\State\SecondHandCategory\Processor\SecondHandCategoryUpdateProcessor;
use App\State\SecondHandCategory\Provider\SecondHandCategoryCreateProvider;
use App\State\SecondHandCategory\Provider\SecondHandCategoryDeleteProvider;
use App\State\SecondHandCategory\Provider\SecondHandCategoryListProvider;
use App\State\SecondHandCategory\Provider\SecondHandCategoryUpdateProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/occasion-categorie', name: 'admin_second_hand_category_')]
class SecondHandCategoryController extends AbstractCrudController
{
    #[Route('s', name: 'list', methods: ['GET'])]
    #[IsGranted('SECOND_HAND_LIST')]
    public function list(
        SecondHandCategoryListProvider $provider,
        Request $request
    ): Response {
        return $this->handleListPaginedAction(
            SecondHandCategoryFilter::class,
            $provider,
            $request
        );
    }

    #[Route('', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('SECOND_HAND_ADD')]
    public function add(
        Request $request,
        SecondHandCategoryCreateProvider $provider,
        SecondHandCategoryCreateProcessor $processor,
    ): Response {
        return $this->handleFormComponentAction(
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
        SecondHandCategory $category
    ): Response {
        return $this->handleFormComponentAction(
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
        SecondHandCategoryDeleteProcessor $processor,
        SecondHandCategoryDeleteProvider $provider,
        SecondHandCategory $category
    ): Response {
        return $this->handleFormComponentAction(
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
        SecondHandCategoryRestoreProcessor $processor,
        SecondHandCategory $category,
    ): Response {
        return $this->handleProcessAction(
            $request,
            $category,
            $processor
        );
    }
}
