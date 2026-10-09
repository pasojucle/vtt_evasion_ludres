<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDialogHandler;
use App\Core\Handler\ActionFormHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Payload\ProductToggleDto;
use App\Entity\Product;
use App\Form\Admin\ProductType;
use App\State\Product\Processor\ProductDeleteProcessor;
use App\State\Product\Processor\ProductRestoreProcessor;
use App\State\Product\Processor\ProductToggleProcessor;
use App\State\Product\Processor\ProductUpdateProcessor;
use App\State\Product\Provider\ProductAdminListProvider;
use App\State\Product\Provider\ProductDeleteProvider;
use App\State\Product\Provider\ProductUpdateProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractCrudController
{
    #[Route('/admin/produits', name: 'admin_product_list', methods: ['GET'])]
    #[IsGranted('PRODUCT_LIST')]
    public function adminList(
        Request $request,
        ProductAdminListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/admin/produit', name: 'admin_product_add', methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_ADD')]
    public function add(
        Request $request,
        ProductUpdateProvider $prrovider,
        ProductUpdateProcessor $processor,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new Product(),
            $prrovider,
            $processor,
            ProductType::class
        );
    }

    #[Route('/admin/produit/{product}', name: 'admin_product', methods: ['GET', 'POST'], requirements:['product' => '\d+'])]
    #[IsGranted('PRODUCT_EDIT', 'product')]
    public function edit(
        Request $request,
        ProductUpdateProvider $prrovider,
        ProductUpdateProcessor $processor,
        ?Product $product,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $product,
            $prrovider,
            $processor,
            ProductType::class
        );
    }

    #[Route('/admin/supprimer/produit/{product}', name: 'admin_product_delete', methods: ['GET', 'POST'])]
    #[IsGranted('PRODUCT_EDIT', 'product')]
    public function adminProduitDelete(
        Request $request,
        ProductDeleteProcessor $processor,
        ProductDeleteProvider $provider,
        Product $product,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
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
