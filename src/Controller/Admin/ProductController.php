<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\Admin\ProductType;
use App\Repository\ProductRepository;
use App\Service\PaginatorService;
use App\Service\Product\ProductEditService;
use App\ViewModel\ProductPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/produits', name: 'admin_products', methods: ['GET'])]
    public function adminList(
        PaginatorService $paginator,
        ProductRepository $productRepository,
        Request $request
    ): Response {
        $query = $productRepository->findAllQuery();
        $products = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('product/admin/list.html.twig', [
            'products' => $products,
            'lastPage' => $paginator->lastPage($products),
        ]);
    }

    #[Route('/admin/produit/{product}', name: 'admin_product', methods: ['GET', 'POST'], defaults:['product' => null])]
    public function adminEdit(
        ProductEditService $productEditService,
        Request $request,
        ProductPresenter $presenter,
        ?Product $product
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $productEditService->execute($form, $request);
            if ($form->isValid()) {
                return $this->redirectToRoute('admin_products');
            }
        }
        $presenter->present($product);

        return $this->render('product/admin/edit.html.twig', [
            'product' => $presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/supprimer/produit/{product}', name: 'admin_product_delete', methods: ['GET', 'POST'])]
    public function adminProduitDelete(
        Request $request,
        ProductPresenter $presenter,
        Product $product
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl(
                'admin_product_delete',
                [
                    'product' => $product->getId(),
                ]
            ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $product->isDisabled(true);
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_products');
        }

        return $this->render('product/admin/delete.modal.html.twig', [
            'product' => $presenter($product)->viewModel(),
            'form' => $form->createView(),
        ]);
    }
}
