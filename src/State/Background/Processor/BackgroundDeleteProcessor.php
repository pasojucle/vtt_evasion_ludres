<?php

declare(strict_types=1);

namespace App\State\Background\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Background\DeleteBackground;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class BackgroundDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteBackground $deleteBackground,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $background = $payload->data;

        ($this->deleteBackground)($background);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'background.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
