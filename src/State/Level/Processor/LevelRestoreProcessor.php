<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\Level;
use App\UseCase\v2\Level\RestoreLevel;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class LevelRestoreProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private RestoreLevel $restoreLevel,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /**
         * @var Level $level
         */
        $level = $payload->data;
        ($this->restoreLevel)($level);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'level.flash.success.restore',
            flashType: 'success',
            data: $level,
        );
    }
}
