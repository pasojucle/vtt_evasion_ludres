<?php

declare(strict_types=1);

namespace App\State\SkillCategory\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\UseCase\v2\SkillCategory\RestoreSkillCategory;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class SkillCategoryRestoreProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private RestoreSkillCategory $restoreSkillCategory,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $category = $payload->data;
        ($this->restoreSkillCategory)($category);
    
        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'skill_category.flash.success.delete',
            flashType: 'success',
            data: $category
        );
    }
}
