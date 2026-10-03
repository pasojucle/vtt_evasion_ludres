<?php

declare(strict_types=1);

namespace App\State\Session\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Dto\Payload\SessionWarningToggle;
use App\UseCase\v2\Session\TogglePresenceSession;

/**
 * @implements TurboStreamProcessorInterface<ActionPayload>
 */
class SessionToggleProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private TogglePresenceSession $togglePresenceSession,
    ) {
    }
    

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /**
         * @var SessionWarningToggle $data
         */
        
        $data = $payload->data;
        $session = $data->session;

        ($this->togglePresenceSession)($session);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'session.flash.success.toogle.present',
            flashType: 'success',
            data: $session->getCluster(),
        );
    }
}
