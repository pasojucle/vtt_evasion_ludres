<?php

declare(strict_types=1);

namespace App\State\Order\Provider;

use App\Core\Contract\Provider\StreamListExportableInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\OrderFilter;
use App\Mapper\Order\OrderAdminListExportMapper;
use App\Repository\OrderHeaderRepository;
use App\State\Order\Trait\OrderListDataTrait;

class OrderAdminListExportProvider implements StreamListExportableInterface
{
    use FilterHydratorTrait;
    use OrderListDataTrait;

    public function __construct(
        private OrderHeaderRepository $orderHeaderRepository,
        private OrderAdminListExportMapper $exportMapper,
    ) {
    }


    public function streamExportContent(AbstractFilter $filter, ?HandlerContext $context = null): void
    {
        /**  @var OrderFilter $filter */
        $entities = $this->getQueryBuilder($filter)->getQuery()->getResult();

        $this->exportMapper->streamToCsv($entities);
    }

    public function filename(HandlerContext|null $context = null): string
    {
        return '';
    }
}
