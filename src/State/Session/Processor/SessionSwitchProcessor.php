<?php

declare(strict_types=1);

namespace App\State\Session\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Dto\Payload\SessionSwitch;
use App\UseCase\v2\Session\SwitchSession;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class SessionSwitchProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private SwitchSession $switchSession,
    ) {
    }
    

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /** @var SessionSwitch $data */
        $data = $payload->data;

        ($this->switchSession)($data->session);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'session.flash.success.switch',
            flashType: 'success',
            data: $data->origin,
        );
    }
}
