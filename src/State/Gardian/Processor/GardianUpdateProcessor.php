<?php

declare(strict_types=1);

namespace App\State\Gardian\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\UseCase\v2\Gardian\UpdateGardian;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class GardianUpdateProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private UpdateGardian $updateGardian,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        ($this->updateGardian)($payload->data);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'member.flash.success.update',
            data: $payload->data,
        );
    }
}
