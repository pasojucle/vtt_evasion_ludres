<?php

declare(strict_types=1);

namespace App\State\Order\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\OrderHeader;
use App\Mapper\Order\OrderAdminMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<OrderHeader>
 */
class OrderCompleteProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private OrderAdminMapper $orderMapper,
        private UrlContextService $urlContextService,
    ) {
    }


    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        return new ListItemUpdateView(
            item: $this->orderMapper->mapToView(
                $data,
                $this->urlContextService->decodeUrl($context->encodedFallback),
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
