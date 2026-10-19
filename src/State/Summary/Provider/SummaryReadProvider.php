<?php

declare(strict_types=1);

namespace App\State\Summary\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Contract\View\ComponentViewInterface;
use App\Core\Dto\HandlerContext;
use App\Entity\BikeRide;
use App\Mapper\Summary\SummaryReadMapper;

/**
 * @implements ComponentProviderInterface<BikeRide>
 */
class SummaryReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private SummaryReadMapper $mapper,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): ComponentViewInterface
    {
        return $this->mapper->mapToView($data, $context);
    }
}
