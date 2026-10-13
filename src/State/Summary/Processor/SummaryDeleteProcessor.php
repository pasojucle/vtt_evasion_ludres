<?php

declare(strict_types=1);

namespace App\State\Summary\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Summary;
use App\UseCase\v2\Summary\DeleteSummary;

    /**
     * @implements RedirectProcessorInterface<ActionPayload>
     */
class SummaryDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private DeleteSummary $deleteSummary,
    ) {
    }


    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /**
         * @var Summary $data
         */
        $data = $payload->data;
        ($this->deleteSummary)($data);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'summary.flash.success.delete',
            targetUrl: $context->encodedFallback,
            flashType: 'success'
        );
    }
}
