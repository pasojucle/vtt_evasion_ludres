<?php

declare(strict_types=1);

namespace App\State\Agreement\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\Agreement;
use App\Mapper\Agreement\AgreementMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<Agreement>
 */
class AgreementToggleProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private AgreementMapper $AgreementMapper,
        private UrlContextService $urlContextService,
    ) {
    }


    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);
    
        return new ListItemUpdateView(
            item: $this->AgreementMapper->mapToView(
                $data,
                $this->urlContextService->decodeUrl($context->encodedFallback),
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
            sortableUrl: $this->urlContextService->generateUrl('admin_agreement_order', ['agreement' => 0], $targetUrl),
        );
    }
}
