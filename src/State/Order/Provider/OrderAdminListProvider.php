<?php

declare(strict_types=1);

namespace App\State\Order\Provider;

use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\OrderFilter;
use App\Dto\View\ListView;
use App\Mapper\Order\OrderAdminListExportMapper;
use App\Mapper\Order\OrderAdminListMapper;
use App\Repository\OrderHeaderRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\ListProviderInterface;
use App\State\StreamExportableInterface;
use Doctrine\ORM\QueryBuilder;

class OrderAdminListProvider implements ListProviderInterface, StreamExportableInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private OrderHeaderRepository $orderHeaderRepository,
        private PaginatorService $paginator,
        private OrderAdminListMapper $mapper,
        private OrderAdminListExportMapper $exportMapper,
    ) {
    }
    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListView
    {
        /** @var OrderFilter $filter */
        $entities = $this->paginator->paginate(
            $this->getQueryBuilder($filter),
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

    public function streamExportContent(AbstractFilter $filter): void
    {
        /**  @var OrderFilter $filter */
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    private function getQueryBuilder(OrderFilter $filter): QueryBuilder
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

        return $qb;
    }
}
