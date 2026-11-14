<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\SecondHandCategory\UpdateSecondHandCategory;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class SecondHandCategoryUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private UpdateSecondHandCategory $updateSecondHandCategory,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $catgory = $payload->data;
        ($this->updateSecondHandCategory)($catgory);

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'secondHandCategory.flash.success.create',
            flashType: 'success',
        );
    }
}
