<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\ProductDtoTransformer;
use App\Dto\Filter\ProductFilter;
use App\Dto\Payload\ProductToggleDto;
use App\Entity\Product;
use App\Form\Admin\ProductType;
use App\Service\Product\ProductEditService;
use App\State\Product\Processor\ProductDeleteProcessor;
use App\State\Product\Processor\ProductRestoreProcessor;
use App\State\Product\Processor\ProductToggleProcessor;
use App\State\Product\Provider\ProductAdminListProvider;
use App\State\Product\Provider\ProductDeleteProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractCrudController
{
    public function __construct(
        private ProductDtoTransformer $productDtoTransformer
    ) {
    }

    #[Route('/admin/produits', name: 'admin_product_list', methods: ['GET'])]
    #[IsGranted('PRODUCT_LIST')]
    public function adminList(
        ProductAdminListProvider $provider,
        Request $request
    ): Response {
        return $this->handleListPaginedAction(
            ProductFilter::class,
            $provider,
            $request
        );
    }

    #[Route('/admin/produit', name: 'admin_product_add', methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_ADD')]
    public function add(
        ProductEditService $productEditService,
        Request $request,
        ?Product $product
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $productEditService->execute($form, $request, true);
            if ($form->isValid()) {
                return $this->redirectToRoute('admin_products');
            }
        }

        return $this->render('product/admin/edit.html.twig', [
            'product' => $this->productDtoTransformer->fromEntity($product),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/produit/{product}', name: 'admin_product', methods: ['GET', 'POST'], requirements:['product' => '\d+'])]
    #[IsGranted('PRODUCT_EDIT', 'product')]
    public function edit(
        ProductEditService $productEditService,
        Request $request,
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

        return $this->render('product/admin/edit.html.twig', [
            'product' => $this->productDtoTransformer->fromEntity($product),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/supprimer/produit/{product}', name: 'admin_product_delete', methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_EDIT', 'product')]
    public function adminProduitDelete(
        Request $request,
        ProductDeleteProcessor $processor,
        ProductDeleteProvider $provider,
        Product $product
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            $product,
            $provider,
            $processor
        );
    }

    #[Route('/restaure/{product}', name: 'admin_product_restore', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLevelRestore(
        Request $request,
        ProductRestoreProcessor $processor,
        Product $product
    ): Response {
        return $this->handleProcessAction(
            $request,
            $product,
            $processor
        );
    }

    #[Route('/admin/toggle/produit/{product}', name: 'admin_product_toggle', methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_EDIT', 'product')]
    public function adminProduitDisbaled(
        Request $request,
        ProductToggleProcessor $processor,
        Product $product
    ): Response {
        return $this->handleComponentProcessAction(
            new ProductToggleDto($product, $request->request->get('csrfToken')),
            $processor
        );
    }
}
