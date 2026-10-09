<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Enum\PublishStatus;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ProductFilter;
use App\Dto\View\ListView;
use App\Mapper\Product\ProductAdminListMapper;
use App\Repository\ProductRepository;
use App\Service\PaginatorService;
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
    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, HandlerContext $context): ListView
    {
        $qb = $this->getQueryBuilder($filter);
        $currentPage = $context->page;

        $entities = $this->paginator->paginate(
            $qb,
            $currentPage,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView($entities, $context, $filter, $filterConfig);
    }

    private function getQueryBuilder(ProductFilter $filter): QueryBuilder
    {
        $qb = $this->productRepository->findProductQuery();

        match ($filter->state) {
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
