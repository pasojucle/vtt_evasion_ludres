<?php

declare(strict_types=1);

namespace App\State\Order\Trait;

use App\Dto\Filter\OrderFilter;
use Doctrine\ORM\QueryBuilder;

trait OrderListDataTrait
{
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

