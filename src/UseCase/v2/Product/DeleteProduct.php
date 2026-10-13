<?php

declare(strict_types=1);

namespace App\UseCase\v2\Product;

use App\Entity\Product;
use App\Repository\Interface\ProductRepositoryInterface;
use App\Service\SoftDeleteService;

class DeleteProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    public function __invoke(Product $product): void
    {
        $this->softDeleteService->softDelete($product);

        $this->productRepository->save($product);
    }
}
