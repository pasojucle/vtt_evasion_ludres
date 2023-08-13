<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\UserDto;
use App\Entity\User;
use App\Dto\ProductDto;
use App\Entity\Product;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ProductDtoTransformer
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
        private Security $security,
        private ProjectDirService $projectDirService
    )
    {
        
    }

    public function fromEntity(?Product $product, ?UserDto $user = null): ProductDto
    {
        $productDto = new ProductDto();
        if ($product) {
            $productDto->id = $product->getId();
            $productDto->name = $product->getName();
            $productDto->content = $product?->getContent();
            $productDto->price = number_format($product->getPrice(), 2) . ' €';
            $productDto->priceClass = 'price';
            $productDto->ref = $product->getRef();
            $productDto->filename = $product->getFilename();
            $productDto->sizes = $this->getSizes($product->getSizes());
            $productDto->pathName = $this->projectDirService->dir('','products', $productDto->filename);
            $productDto->pathNameForPdf = $this->projectDirService->dir('products', $productDto->filename);
            $productDto->sellingPrice = $product->getPrice();
            $productDto->discountPrice = null;
            $productDto->discountTitle = null;

            /** @var ?User $userConnected */
            $userConnected = $this->security->getUser();
            if (null === $user && $userConnected) {
                $user = $this->userDtoTransformer->fromEntity($userConnected);
            }
            
            if (null !== $user && $user instanceof UserDto) {
                if (!empty($user->member) && $product->getCategory() === $user->lastLicence->category) {
                    $productDto->sellingPrice = $product->getDiscountPrice();
                    $productDto->discountPrice = number_format($product->getDiscountPrice(), 2) . ' €';
                    $productDto->priceClass = 'throughed-price';
                    $productDto->discountTitle = $product->getDiscountTitle();
                }
            }
        }
        

        return $productDto;
    }

    public function fromEntities(Paginator|array|Collection $productEntities): array
    {
        $products = [];
        foreach ($productEntities as $productEntity) {
            $products[] = $this->fromEntity($productEntity);
        }

        return $products;
    }


    public function getSizes(?Collection $productSizes): array
    {
        $sizes = [];
            foreach ($productSizes as $size) {
                $sizes[] = $size->getName();
            }
        return $sizes;
    }
}
