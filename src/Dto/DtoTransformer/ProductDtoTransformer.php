<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ProductDto;
use App\Dto\UserDto;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Member;
use App\Entity\Product;
use App\Mapper\DropdownMapper;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;

class ProductDtoTransformer
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
        private Security $security,
        private ProjectDirService $projectDirService,
        private DropdownMapper $dropdownMapper,
    ) {
    }

    public function fromEntity(?Product $product, ?UserDto $member = null): ProductDto
    {
        $productDto = new ProductDto();
        if ($product) {
            $productDto->id = $product->getId();
            $productDto->name = $product->getName();
            $productDto->content = $product->getContent();
            $productDto->price = number_format($product->getPrice(), 2) . ' €';
            $productDto->priceClass = 'price';
            $productDto->ref = $product->getRef();
            $productDto->filename = $product->getFilename();
            $productDto->sizes = $this->getSizes($product->getSizes());
            $productDto->pathName = $this->projectDirService->dir('', 'products', $productDto->filename);
            $productDto->pathNameForPdf = $this->projectDirService->dir('products', $productDto->filename);
            $productDto->sellingPrice = $product->getPrice();
            $productDto->discountPrice = null;
            $productDto->discountTitle = null;
            $productDto->isDisabled = $product->isDisabled();

            /** @var ?Member $memberConnected */
            $memberConnected = $this->security->getUser();
            if (null === $member && $memberConnected) {
                $member = $this->userDtoTransformer->fromEntity($memberConnected);
            }
            
            if (null !== $member && $member instanceof UserDto) {
                if (!empty($member->member) && $product->getCategory() === LicenceCategoryEnum::SCHOOL && LicenceCategoryEnum::SCHOOL === $member->lastLicence->category) {
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

    public function listFromEntities(Paginator|array|Collection $productEntities): array
    {
        $products = [];
        foreach ($productEntities as $productEntity) {
            $productDto = new ProductDto();
            $productDto->id = $productEntity->getId();
            $productDto->name = $productEntity->getName();
            $productDto->isDisabled = $productEntity->isDisabled();
            $productDto->dropdown = $this->dropdownMapper->fromProduct($productEntity);
            $products[] = $productDto;
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
