<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Dto\Enum\PublishStatus;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ProductFilter;
use App\Dto\View\ListView;
use App\Mapper\Product\ProductAdminListMapper;
use App\Repository\ProductRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\Interface\ListProviderInterface;
use Doctrine\ORM\QueryBuilder;


class ProductAdminListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;
    public function __construct(
        private ProductRepository $productRepository,
        private PaginatorService $paginator,
        private ProductAdminListMapper $mapper,
    ) {
    }
    
    /**
     * @param ProductFilter $filter
     */
    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
        $qb = $this->getQueryBuilder($filter);

        $entities = $this->paginator->paginate(
            $qb,
            $currentPage,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView($entities, $route, $currentPage, $filter, $filterConfig);
    }

    private function getQueryBuilder(ProductFilter $filter): QueryBuilder
    {
        $qb = $this->productRepository->findProductQuery();

        match($filter->state) {
            PublishStatus::ENABLED => $this->productRepository->filterEnabled($qb),
            PublishStatus::DISABLED => $this->productRepository->filterDisabled($qb),
            default => null,
        };

        if ($filter->partNumber) {
            $this->productRepository->filterPartNumber($qb, $filter->partNumber);
        }

        if (!$filter->showDeleted) {
            $this->productRepository->filterActive($qb);
        }

        if ($filter->sort) {
            $this->productRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
