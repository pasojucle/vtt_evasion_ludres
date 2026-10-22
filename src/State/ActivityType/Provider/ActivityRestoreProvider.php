<?php

declare(strict_types=1);

namespace App\State\ActivityType\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\ActivityType\ActivityTypeView;
use App\Entity\BikeRideType;
use App\Mapper\ActivityType\ActivityTypeMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<BikeRideType>
 */
class ActivityRestoreProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private ActivityTypeMapper $ActivityTypeMapper,
        private UrlContextService $urlContextService,
    ) {
    }


    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ActivityTypeView
    {
        return new ActivityTypeView(
            $this->ActivityTypeMapper->mapToView(
                $data,
                $this->urlContextService->decodeUrl($context->encodedFallback),
            )
        );
    }
}
