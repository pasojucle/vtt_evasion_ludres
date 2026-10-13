<?php

declare(strict_types=1);

namespace App\State\Certificate\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class MemberCertificateCreateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        return new RedirectProcessorResult(
            success: true,
            messageKey: 'member_certificate.flash.success.create',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
        );
    }
}
