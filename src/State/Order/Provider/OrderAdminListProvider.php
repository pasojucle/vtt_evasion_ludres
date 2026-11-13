<?php

declare(strict_types=1);

namespace App\State\Order\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\OrderFilter;
use App\Dto\View\ListView;
use App\Mapper\Order\OrderAdminListMapper;
use App\Repository\OrderHeaderRepository;
use App\Service\PaginatorService;
use App\State\Order\Trait\OrderListDataTrait;

class OrderAdminListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;
    use OrderListDataTrait;

    public function __construct(
        private OrderHeaderRepository $orderHeaderRepository,
        private PaginatorService $paginator,
        private OrderAdminListMapper $mapper,
    ) {
    }
    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, HandlerContext $context): ListView
    {
        /** @var OrderFilter $filter */
        $entities = $this->paginator->paginate(
            $this->getQueryBuilder($filter),
            $context->page,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $context,
            $filter,
            $filterConfig
        );
    }
}
