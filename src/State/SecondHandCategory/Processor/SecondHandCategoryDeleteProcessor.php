<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\SecondHandCategory\DeleteSecondHandCategory;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class SecondHandCategoryDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private DeleteSecondHandCategory $deleteSecondHandCategory,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $category = $payload->data;
        ($this->deleteSecondHandCategory)($category);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'category.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
