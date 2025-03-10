<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\ProductDtoTransformer;
use App\Entity\Product;
use App\Form\OrderLineAddType;
use App\Repository\ProductRepository;
use App\Service\Order\OrderAddService;
use App\Service\PaginatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductDtoTransformer $productDtoTransformer,
    ) {
    }

    #[Route('/boutique', name: 'products', methods: ['GET'])]
    public function list(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        ProductRepository $productRepository,
        Request $request
    ): Response {
        $query = $productRepository->findAllQuery();
        $products = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('product/list.html.twig', [
            'products' => $this->productDtoTransformer->fromEntities($products),
            'paginator' => $paginatorDtoTransformer->fromEntities($products),
        ]);
    }

    #[Route('/boutique/produit/{product}', name: 'product_show', methods: ['GET', 'POST'])]
    public function show(
        OrderAddService $orderAddService,
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

        return $this->render('product/show.html.twig', [
            'product' => $this->productDtoTransformer->fromEntity($product),
            'form' => $form->createView(),
        ]);
    }
}
