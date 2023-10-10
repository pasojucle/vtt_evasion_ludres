<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\Admin\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/param/categorie', name: 'admin_category_')]
#[IsGranted('ROLE_ADMIN')]
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

    #[Route('supprimer/{category}', name: 'delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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
