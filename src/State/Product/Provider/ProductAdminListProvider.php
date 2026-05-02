<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Dto\Filter\ProductFilter;
use App\Dto\ListDto;
use App\Mapper\Product\ProductAdminListMapper;
use App\Repository\ProductRepository;
use App\Service\PaginatorService;

class ProductAdminListProvider
{
        public function __construct(
        private ProductRepository $productRepository,
        private PaginatorService $paginator,
        private ProductAdminListMapper $mapper,
    )
    {

    }
    
    public function getCollection(ProductFilter $filter, string $route, ?int $currentPage = 1): ListDto
    {
        $entities = $this->paginator->paginate(
            $this->productRepository->findProductQuery($filter->state), 
            $currentPage, 
            PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView($entities, $route, $currentPage, $filter);
    }
}