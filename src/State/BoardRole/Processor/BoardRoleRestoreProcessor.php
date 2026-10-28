<?php

declare(strict_types=1);

namespace App\State\BoardRole\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Entity\BoardRole;
use App\UseCase\v2\BoardRole\RestoreBoardRole;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class BoardRoleRestoreProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private RestoreBoardRole $restoreBoardRole,
    ) {
    }

        public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        /**
         * @var BoardRole $boardRole
         */
        $boardRole = $payload->data;
        ($this->restoreBoardRole)($boardRole);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: 'boardRole.flash.success.restore',
            flashType: 'success',
            data: $boardRole,
        );
    }
}
