<?php

declare(strict_types=1);

namespace App\UseCase\Product;

use App\Dto\DtoTransformer\PaginatorDtoTransformer;
use App\Dto\DtoTransformer\ProductDtoTransformer;
use App\Repository\ProductRepository;
use App\Service\PaginatorService;
use Symfony\Component\HttpFoundation\Request;

class GetProductList
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductDtoTransformer $productDtoTransformer,
        private PaginatorService $paginator,
        private PaginatorDtoTransformer $paginatorDtoTransformer,
    ) {
    }

    public function execute(Request $request): array
    {
        $query = $this->productRepository->findAllQuery();
        $products = $this->paginator->paginate($query, $request->query->getInt('page', 1), PaginatorService::PAGINATOR_PER_PAGE);

        return [
            'products' => $this->productDtoTransformer->listFromEntities($products),
            'paginator' => $this->paginatorDtoTransformer->fromEntities($products),
        ];
    }
}
