<?php

declare(strict_types=1);

namespace App\UseCase\Product;

use App\Dto\DtoTransformer\DropdownDtoTransformer;
use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Repository\ProductRepository;
use App\Dto\DtoTransformer\ProductDtoTransformer;
use App\Service\PaginatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetProductList
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductDtoTransformer $productDtoTransformer,
        private PaginatorService $paginator,
        private PaginatorDtoTransformer $paginatorDtoTransformer,
        private DropdownDtoTransformer $dropdownDtoTransformer,
        private UrlGeneratorInterface $urlGenerator,
    ) 
    {

    }

    public function execute(Request $request): array
    {
        $query = $this->productRepository->findAllQuery();
        $products = $this->paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return [
            'products' => $this->productDtoTransformer->listFromEntities($products),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($products),
        ];
    }
}