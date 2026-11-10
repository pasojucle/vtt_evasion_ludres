<?php

declare(strict_types=1);

namespace App\State\LicenceAuthorization\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\LicenceAuthorization\LicenceAuthorizationsView;
use App\Entity\Licence;

use App\Mapper\LicenceAuthorization\LicenceAuthorizationsReadMapper;

/**
 * @implements ComponentProviderInterface<Licence>
 */
class LicenceAuthorizationsReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private LicenceAuthorizationsReadMapper $licenceAuthorizationsMapper,
    ) {
    }

    public function getView(object $entity, ?HandlerContext $context = null): LicenceAuthorizationsView
    {
        return $this->licenceAuthorizationsMapper->mapToView($entity);
    }
}
