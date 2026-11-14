<?php

declare(strict_types=1);

namespace App\State\SecondHandCategory\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\UseCase\v2\SecondHandCategory\RestoreSecondHandCategory;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class SecondHandCategoryRestoreProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private RestoreSecondHandCategory $restoreSecondHandCategory,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $category = $payload->data;
        ($this->restoreSecondHandCategory)($category);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'category.flash.success.restore',
            flashType: 'success',
            data: $category,
        );
    }
}
