<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\SkillCategory\UpdateSkillCategory;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class SkillCategoryUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private UpdateSkillCategory $updateSkillCategory,
    ) {
    }


    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $category = $payload->data;
        ($this->updateSkillCategory)($category);

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'skillCategory.flash.success.update',
            flashType: 'success',
        );
    }
}
