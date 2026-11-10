<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Dto\Payload\LicenceReject;
use App\Service\UrlContextService;
use App\UseCase\v2\Licence\RejectLicence;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class LicenceRejectProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private RejectLicence $updateLicenceState,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        /**
         * @var LicenceReject $data
         */
        $data = $payload->data;
        $licence = $data->licence;
        
        if (($this->updateLicenceState)($licence, $data->content)) {
            return new RedirectProcessorResult(
                success: true,
                targetUrl: $targetUrl,
                messageKey: 'registration.flash.success.reject',
                flashType: 'success',
            );
        }

        return new RedirectProcessorResult(
            success: false,
            targetUrl: $targetUrl,
            messageKey: 'registration.flash.error.reject',
            flashType: 'danger',
        );
    }
}
