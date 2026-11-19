<?php

declare(strict_types=1);

namespace App\State\Coverage\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Licence\ValidateCoverage;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class CoverageValidateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private ValidateCoverage $validateCoverage,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $licence = $payload->data;
        
        ($this->validateCoverage)($licence);

        return new RedirectProcessorResult(
            success: false,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'coverage.flash.success.valided',
            flashType: 'success',
        );
    }
}
