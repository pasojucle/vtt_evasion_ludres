<?php

declare(strict_types=1);

namespace App\State\Notification\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\Notification;
use App\Mapper\Notification\NotificationMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<Notification>
 */
class NotificationToggleProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private NotificationMapper $NotificationMapper,
        private UrlContextService $urlContextService,
    ) {
    }


    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        return new ListItemUpdateView(
            item: $this->NotificationMapper->mapToView(
                $data,
                $this->urlContextService->decodeUrl($context->encodedFallback),
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
