<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Licence;
use App\Service\UrlContextService;
use App\UseCase\v2\Licence\ReceiveLicence;
use App\UseCase\v2\Licence\RejectLicence;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class LicenceReceiveProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private ReceiveLicence $receiveLicence,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        /** @var Licence $licence */
        $licence = $payload->data;
        if (($this->receiveLicence)($licence)) {
            return new RedirectProcessorResult(
                success: true,
                targetUrl: $targetUrl,
                messageKey: 'registration.flash.success.received',
                flashType: 'success',
            );
        }

        return new RedirectProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.error.received',
            flashType: 'danger',
        );
    }
}
