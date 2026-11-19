<?php

declare(strict_types=1);

namespace App\State\Link\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Link\DeleteLink;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class LinkDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteLink $deleteLink,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $link = $payload->data;

        ($this->deleteLink)($link);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'link.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
