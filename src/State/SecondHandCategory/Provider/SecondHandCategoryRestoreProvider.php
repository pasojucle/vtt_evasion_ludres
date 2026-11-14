<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\SecondHandCategory;
use App\Mapper\SecondHandCategory\SecondHandCategoryMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<SecondHandCategory>
 */
class SecondHandCategoryRestoreProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private SecondHandCategoryMapper $secondHandCategoryMapper,
    ) {
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        return new ListItemUpdateView(
            item: $this->secondHandCategoryMapper->mapToView(
                $data,
                $targetUrl,
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
