<?php

declare(strict_types=1);

namespace App\UseCase\v2\Product;

use App\Entity\Product;
use App\Repository\Interface\ProductRepositoryInterface;
use App\Service\UploadService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private UploadService $uploadService,
    ) {
    }

    public function __invoke(Product $product, array $files): void
    {
        $this->uploadMedia($files, $product);

        $this->productRepository->save($product);
    }


    /** @param UploadedFile[] $files */
    private function uploadMedia(array $files, Product $product): void
    {
        $file = $files['file'] ?? null;
        if ($file) {
            $product->setFileName($this->uploadService->uploadFile($file, $product));
        }
    }
}
