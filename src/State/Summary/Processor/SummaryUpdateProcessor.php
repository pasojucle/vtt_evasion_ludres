<?php

declare(strict_types=1);

namespace App\State\Summary\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Summary\UpdateSummary;

class SummaryUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateSummary $updateSummary,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $summary = $payload->data;
        $id = $summary->getId();
        
        ($this->updateSummary)($summary);

        $messageKey = 'summary.flash.success.edit';
        if (!$id) {
            $messageKey = 'summary.flash.success.create';
        }
        
        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
