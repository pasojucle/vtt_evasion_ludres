<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\Admin\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/categorie', name: 'admin_category_')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('s', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('category/admin/list.html.twig', [
            'categories' => $this->categoryRepository->findAllAsc(),
        ]);
    }

    #[Route('/{category}', name: 'edit', methods: ['GET', 'POST'], defaults:['category' => null])]
    public function edit(
        Request $request,
        ?Category $category
    ): Response {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $this->categoryRepository->save($category, true);

            return $this->redirectToRoute('admin_category_list');
        }

        return $this->render('category/admin/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/supprimer/category/{category}', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        Category $category
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_category_delete',
                [
                    'category' => $category->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->categoryRepository->remove($category, true);

            return $this->redirectToRoute('admin_category_list');
        }

        return $this->render('category/admin/delete.modal.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }
}
