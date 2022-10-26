<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Product;
use App\Entity\User;
use App\ViewModel\UserViewModel;
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

    public ?string $pathName = null;

    public string $pathNameForPdf;

    public ?array $sizes;

    private ?Collection $productSizes;

    /**@param ?UserViewModel $user */
    public static function fromProduct(Product $product, ServicesPresenter $services, ?UserViewModel $user = null)
    {
        $productView = new self();
        $productView->id = $product->getId();
        $productView->name = $product->getName();
        $productView->content = $product->getContent();
        $productView->price = number_format($product->getPrice(), 2) . ' €';
        $productView->priceClass = 'price';
        $productView->ref = $product->getRef();
        $productView->filename = $product->getFilename();
        $productView->productSizes = $product->getSizes();
        $productView->pathName = DIRECTORY_SEPARATOR . $services->productDirectory . DIRECTORY_SEPARATOR . $productView->filename;
        $productView->pathNameForPdf = $services->productDirectory . DIRECTORY_SEPARATOR . $productView->filename;
        $productView->sellingPrice = $product->getPrice();
        $productView->price = number_format($product->getPrice(), 2) . ' €';
        $productView->discountPrice = null;
        $productView->discountTitle = null;

        /** @var ?User $userConnected */
        $userConnected = $services->security->getUser();
        if (null === $user && $userConnected) {
            $user = UserViewModel::fromUser($userConnected, $services);
        }
        
        if (null !== $user && $user instanceof UserViewModel) {
            if (!empty($user->member) && $product->getCategory() === $user->lastLicence->category) {
                $productView->sellingPrice = $product->getDiscountPrice();
                $productView->discountPrice = number_format($product->getDiscountPrice(), 2) . ' €';
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
            foreach ($this->productSizes as $size) {
                $sizes[] = $size->getName();
            }
        }

        return $sizes;
    }
}
