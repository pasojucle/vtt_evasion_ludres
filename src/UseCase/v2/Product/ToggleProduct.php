<?php

declare(strict_types=1);

namespace App\UseCase\v2\Product;

use App\Entity\Product;
use App\Repository\Interface\ProductRepositoryInterface;
use App\Service\DisableService;

class ToggleProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private DisableService $disableService,
    ) {
    }

    public function __invoke(Product $product): void
    {
        $this->disableService->toggle($product);
        
        $this->productRepository->save($product);
    }
}
