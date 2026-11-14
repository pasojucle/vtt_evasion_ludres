<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\SkillCategory\DeleteSkillCategory;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class SkillCategoryDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteSkillCategory $deleteSkillCategory,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $category = $payload->data;
        ($this->deleteSkillCategory)($category);
    
        return new RedirectProcessorResult(
            success: true,
            messageKey: 'skill_category.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
