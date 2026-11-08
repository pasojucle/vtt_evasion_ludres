<?php

declare(strict_types=1);

namespace App\State\Message\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Message;
use App\Service\UrlContextService;
use App\UseCase\v2\Message\DeleteMessage;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class MessageDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteMessage $deleteMessage,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /**
         * @var Message $message
         */
        $message = $payload->data;
        ($this->deleteMessage)($message);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'message.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
