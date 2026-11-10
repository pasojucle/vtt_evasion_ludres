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
use App\State\Interface\FormRedirectProcessorInterface;
use App\UseCase\v2\Licence\DeleteLicence;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class LicenceDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteLicence $deleteLicence,
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<Licence>
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $licence = $payload->data;
        ($this->deleteLicence)($licence);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'licence.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
