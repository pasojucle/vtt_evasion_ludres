<?php

declare(strict_types=1);

namespace App\State\Order\Provider;

use App\Dto\Filter\OrderFilter;
use App\Dto\ListDto;
use App\Mapper\Order\OrderAdminListMapper;
use App\Repository\OrderHeaderRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;

class OrderAdminListProvider
{
    use FilterHydratorTrait;

    public function __construct(
        private OrderHeaderRepository $orderHeaderRepository,
        private PaginatorService $paginator,
        private OrderAdminListMapper $mapper,
    ) {

    }
    public function getCollection(OrderFilter $filter,  FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListDto
    {
        $qb = $this->orderHeaderRepository->findOrdersQuery();

        if ($filter->status) {
            $this->orderHeaderRepository->filterStatus($qb, $filter->status);
        }

        if ($filter->member) {
            $this->orderHeaderRepository->filterMember($qb, $filter->member);
        }

        if ($filter->sort) {
            $this->orderHeaderRepository->filterSort($qb, $filter->sort);
        }

        $entities = $this->paginator->paginate(
            $qb,
            $currentPage,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE

        );

        return $this->mapper->mapToView(
            $entities, 
            $route, 
            $currentPage, 
            $filter,
            $filterConfig
        );
    }
}
