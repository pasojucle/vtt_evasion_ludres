<?php

declare(strict_types=1);

namespace App\State\Agreement\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Agreement;
use App\Service\UrlContextService;
use App\UseCase\v2\Agreement\UpdateAgreement;

class AgreementUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateAgreement $updateAgreement,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /** @var Agreement $agreement*/
        $agreement = $payload->data;
        $id = $agreement->getId();

        ($this->updateAgreement)($agreement);

        $messageKey = 'agreement.flash.success.edit';
        if (!$id) {
            $messageKey = 'agreement.flash.success.create';
        }

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
