<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\ProductDtoTransformer;
use App\Entity\Product;
use App\Form\Admin\ProductType;
use App\Repository\ProductRepository;
use App\Service\PaginatorService;
use App\Service\Product\ProductEditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductDtoTransformer $productDtoTransformer
    )
    {

    }

    #[Route('/admin/produits', name: 'admin_products', methods: ['GET'])]
    public function adminList(
        PaginatorService $paginator,
        PaginatorDtoTransformer $paginatorDtoTransformer,
        ProductRepository $productRepository,
        Request $request
    ): Response {
        $query = $productRepository->findAllQuery();
        $products = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('product/admin/list.html.twig', [
            'products' => $products,
            'paginator' => $paginatorDtoTransformer->fromEntities($products),
        ]);
    }

    #[Route('/admin/produit/{product}', name: 'admin_product', methods: ['GET', 'POST'], defaults:['product' => null])]
    public function adminEdit(
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
    public function adminProduitDelete(
        Request $request,
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
            $product->setIsDisabled(true);
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_products');
        }

        return $this->render('product/admin/delete.modal.html.twig', [
            'product' => $this->productDtoTransformer->fromEntity($product),
            'form' => $form->createView(),
        ]);
    }
}
