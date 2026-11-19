<?php

declare(strict_types=1);

namespace App\State\SecondHand\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\SecondHand\DeleteSecondHand;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class SecondHandDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteSecondHand $deleteSecondHand,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $secondHand = $payload->data;

        ($this->deleteSecondHand)($secondHand);

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'second_hand.flash.success.delete',
        );
    }
}
