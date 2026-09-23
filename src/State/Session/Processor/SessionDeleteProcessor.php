<?php

declare(strict_types=1);

namespace App\State\Session\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\UseCase\v2\Session\RemoveSession;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class SessionDeleteProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private RemoveSession $removeSession,
    ) {
    }
    

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $data = $payload->data;

        ($this->removeSession)($data->session);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'session.flash.success.delete',
            flashType: 'success',
            data: $data->cluster,
        );
    }
}
