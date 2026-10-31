<?php

declare(strict_types=1);

namespace App\State\Product\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\Product;
use App\Mapper\Product\ProductMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<Product>
 */
class ProductToggleProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private ProductMapper $ProductMapper,
        private UrlContextService $urlContextService,
    ) {
    }


    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        return new ListItemUpdateView(
            item: $this->ProductMapper->mapToView(
                $data,
                $this->urlContextService->decodeUrl($context->encodedFallback),
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
