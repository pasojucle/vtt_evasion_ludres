<?php

declare(strict_types=1);

namespace App\State\ActivityType\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Dto\View\ListItemView;
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


    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        return new ListItemUpdateView(
            item: $this->ActivityTypeMapper->mapToView(
                $data,
                $this->urlContextService->decodeUrl($context->encodedFallback),
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
