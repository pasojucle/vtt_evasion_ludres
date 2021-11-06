<?php

namespace App\ViewModel;

use App\Entity\User;
use ReflectionClass;
use App\Entity\Product;
use App\Service\LicenceService;
use Doctrine\Common\Collections\Collection;

class ProductViewModel extends AbstractViewModel
{
    public ?int $id;
    public ?string $name;
    public ?string $content;
    public ?string $price;
    public ?string $priceClass;
    public ?string $discountPrice;
    public ?string $discountTitle;
    public ?float $sellingPrice;
    public ?string $ref;
    public ?string $filename;
    private ?Collection $productSizes;
    public ?string $pathName = null;
    public ?array $sizes;

    public static function fromProduct(Product $product, array $data)
    {
        $productView = new self();
        $productView->id = $product->getId();
        $productView->name = $product->getName();
        $productView->content = $product->getContent();
        $productView->price = number_format($product->getPrice(), 2).' €';
        $productView->priceClass = 'price';
        $productView->ref = $product->getRef();
        $productView->filename = $product->getFilename();
        $productView->productSizes = $product->getSizes();
        $productView->pathName = DIRECTORY_SEPARATOR.$data['productDirectory'].DIRECTORY_SEPARATOR.$productView->filename;
        $productView->pathNameForPdf = $data['productDirectory'].DIRECTORY_SEPARATOR.$productView->filename;
        $productView->sellingPrice = $product->getPrice();
        $productView->price = number_format($product->getPrice(), 2).' €';
        $productView->discountPrice = null;
        $productView->discountTitle = null;
        
        if (null !== $data['user']) {
            $user = UserViewModel::fromUser($data['user'], $data);
            
            if (!empty($user->getMember()) && $product->getCategory() === $user->getLastLicence()['category']) {
                $productView->sellingPrice = $product->getDiscountPrice();
                $productView->discountPrice = number_format($product->getDiscountPrice(), 2).' €';
                $productView->priceClass = 'throughed-price';
                $productView->discountTitle = $product->getDiscountTitle();
            }
        }

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