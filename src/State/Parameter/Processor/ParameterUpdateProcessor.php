<?php

declare(strict_types=1);

namespace App\State\Parameter\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\ParameterService;
use App\Service\UrlContextService;
use App\UseCase\v2\Parameter\UpdateParameter;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class ParameterUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateParameter $updateParameter,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $data = $payload->data;
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);
        ($this->updateParameter)($data);

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $targetUrl,
            messageKey: 'Parameter.flash.success.Parameter',
            flashType: 'success',
        );
    }
}
