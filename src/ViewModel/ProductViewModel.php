<?php

namespace App\ViewModel;

use App\Entity\Product;
use Doctrine\Common\Collections\Collection;
use ReflectionClass;

class ProductViewModel extends AbstractViewModel
{
    public ?int $id;
    public ?string $name;
    public ?string $content;
    public ?string $price;
    public ?string $ref;
    public ?string $filename;
    private ?Collection $productSizes;
    public ?string $pathName = null;
    public ?array $sizes;

    public static function fromProduct(Product $product, string $productDirectory)
    {
        $productView = new self();
        $productView->id = $product->getId();
        $productView->name = $product->getName();
        $productView->content = $product->getContent();
        $productView->price = number_format($product->getPrice(), 2).' €';
        $productView->ref = $product->getRef();
        $productView->filename = $product->getFilename();
        $productView->productSizes = $product->getSizes();
        $productView->pathName = $productDirectory.DIRECTORY_SEPARATOR.$productView->filename;

        return $productView;
    }

    public function getSizes(): array
    {
        $sizes = [];
        if (!$this->productSizes->isEmpty()) {
            foreach($this->productSizes as $size) {
                $sizes[] = $size->getName();
            }
        }

        return $sizes;
    }
}