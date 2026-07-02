<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\SecondHandCategoryFilter;
use App\Entity\SecondHandCategory;
use App\Form\Admin\SecondHandCategoryType;
use App\Repository\SecondHandCategoryRepository;
use App\State\SecondHandCategory\Processor\SecondHandCategoryDeleteProcessor;
use App\State\SecondHandCategory\Processor\SecondHandCategoryUpdateProcessor;
use App\State\SecondHandCategory\Provider\SecondHandCategoryDeleteProvider;
use App\State\SecondHandCategory\Provider\SecondHandCategoryListProvider;
use App\State\SecondHandCategory\Provider\SecondHandCategoryUpdateProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/param/occasion-categorie', name: 'admin_second_hand_category_')]
#[IsGranted('ROLE_ADMIN')]
class SecondHandCategoryController extends AbstractCrudController
{
    public function __construct(
        private SecondHandCategoryRepository $categoryRepository,
    ) {
    }

    #[Route('s', name: 'list', methods: ['GET'])]
    public function list(
        SecondHandCategoryListProvider $provider,
        Request $request
    ): Response {
        return $this->handleListAction(
            SecondHandCategoryFilter::class,
            $provider,
            $request
        );
    }

    #[Route('', name: 'add', methods: ['GET', 'POST'])]
    public function add(
        Request $request,
    ): Response {
        $form = $this->createForm(SecondHandCategoryType::class, null, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), )
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $this->categoryRepository->save($category, true);

            return $this->redirectToRoute('admin_category_list');
        }

        return $this->render('category/admin/edit.html.twig', [
            'category' => null,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{category}', name: 'edit', methods: ['GET', 'POST'], defaults:['category' => null])]
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
}
