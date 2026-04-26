<?php

declare(strict_types= 1);

namespace App\State\Order\Provider;

use App\Dto\Filter\OrderFilter;
use App\Dto\ListDto;
use App\Mapper\Order\OrderAdminListMapper;
use App\Repository\OrderHeaderRepository;
use App\Service\PaginatorService;


class OrderAdminListProvider
{
    public function __construct(
        private OrderHeaderRepository $orderHeaderRepository,
        private PaginatorService $paginator,
        private OrderAdminListMapper $mapper,
    )
    {

    }
    
    public function getCollection(OrderFilter $filter, string $route, ?int $currentPage = 1): ListDto
    {
        $entities = $this->paginator->paginate(
            $this->orderHeaderRepository->findOrdersQuery($filter->status), 
            $currentPage, 
            PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView($entities, $route, $currentPage, $filter);
    }
}

