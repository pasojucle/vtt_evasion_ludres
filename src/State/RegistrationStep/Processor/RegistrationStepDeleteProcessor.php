<?php

declare(strict_types=1);

namespace App\State\RegistrationStep\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\RegistrationStep\DeleteRegistrationStep;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class RegistrationStepDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private DeleteRegistrationStep $deleteRegistrationStep,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $registrationStep = $payload->data;

        ($this->deleteRegistrationStep)($registrationStep);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'message.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
