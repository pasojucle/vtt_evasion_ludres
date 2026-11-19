<?php

declare(strict_types=1);

namespace App\State\Documentation\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Documentation\DeleteDocumentation;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class DocumentationDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteDocumentation $deleteDocumentation,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $documentation = $payload->data;

        ($this->deleteDocumentation)($documentation);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'documentation.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
