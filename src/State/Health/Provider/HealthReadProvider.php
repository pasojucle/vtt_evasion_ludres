<?php

declare(strict_types=1);

namespace App\State\Health\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Health\HealthView;
use App\Entity\Health;
use App\Mapper\Health\HealthReadMapper;

/**
 * @implements ComponentProviderInterface<Health>
 */
class HealthReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private HealthReadMapper $identityReadMapper,
    ) {
    }

    public function getView(object $entity, ?HandlerContext $context = null): HealthView
    {
        return $this->identityReadMapper->mapToView($entity);
    }
}
