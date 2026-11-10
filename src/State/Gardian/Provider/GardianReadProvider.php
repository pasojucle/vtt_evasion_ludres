<?php

declare(strict_types=1);

namespace App\State\Gardian\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Gardian\GardianView;
use App\Entity\MemberGardian;
use App\Mapper\Gardian\GardianReadMapper;

/**
 * @implements ComponentProviderInterface<MemberGardian>
 */
class GardianReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private GardianReadMapper $gardianReadMapper,
    ) {
    }

    public function getView(object $entity, ?HandlerContext $context = null): GardianView
    {
        return $this->gardianReadMapper->mapToView($entity);
    }
}
