<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Form\OrderLineAddType;
use App\Repository\ProductRepository;
use App\Service\Order\OrderAddService;
use App\Service\PaginatorService;
use App\ViewModel\ProductPresenter;
use App\ViewModel\ProductsPresenter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/boutique', name: 'products', methods: ['GET'])]
    public function list(
        PaginatorService $paginator,
        ProductRepository $productRepository,
        ProductsPresenter $presenter,
        Request $request
    ): Response {
        $query = $productRepository->findAllQuery();
        $products = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $presenter->present($products);

        return $this->render('product/list.html.twig', [
            'products' => $presenter->viewModel()->products,
            'lastPage' => $paginator->lastPage($products),
        ]);
    }

    #[Route('/boutique/produit/{product}', name: 'product_show', methods: ['GET', 'POST'])]
    public function show(
        OrderAddService $orderAddService,
        ProductPresenter $presenter,
        Request $request,
        Product $product
    ): Response {
        $form = $this->createForm(OrderLineAddType::class);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $orderAddService->execute($product, $form);
            if ($form->isValid()) {
                return $this->redirectToRoute('order_edit');
            }
        }
        $presenter->present($product);

        return $this->render('product/show.html.twig', [
            'product' => $presenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }
}
