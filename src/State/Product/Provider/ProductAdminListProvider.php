<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Dto\Filter\ProductFilter;
use App\Dto\ListDto;
use App\Mapper\Product\ProductAdminListMapper;
use App\Repository\ProductRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;

class ProductAdminListProvider
{
    use FilterHydratorTrait;
    public function __construct(
        private ProductRepository $productRepository,
        private PaginatorService $paginator,
        private ProductAdminListMapper $mapper,
    ) {
    }
    
    public function getCollection(ProductFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListDto
    {
        $qb = $this->productRepository->findProductQuery();

        if ($filter->state) {
            $this->productRepository->filterState($qb, $filter->state);
        }

        if ($filter->partNumber) {
            $this->productRepository->filterPartNumber($qb, $filter->partNumber);
        }

        if ($filter->sort) {
            $this->productRepository->filterSort($qb, $filter->sort);
        }

        $entities = $this->paginator->paginate(
            $qb,
            $currentPage,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE

        );

        return $this->mapper->mapToView($entities, $route, $currentPage, $filter, $filterConfig);
    }
}
