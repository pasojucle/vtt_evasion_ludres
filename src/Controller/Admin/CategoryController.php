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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        $form = $this->createForm(CategoryType::class, $category, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params'), )
        ]);
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
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $category->setDeleted(true);
                $this->categoryRepository->save($category, true);

                return $this->redirectToRoute('admin_category_list');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('component/destructive.modal.html.twig', [
            'form' => $form->createView(),
            'title' => 'Supprimer une cathégorie',
            'content' => sprintf('Etes vous certain de supprimer la catégorie %s', $category->getName()),
            'btn_label' => 'Supprimer',
        ], $response);
    }
}
