<?php

declare(strict_types=1);

namespace App\State\BoardRole\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\BoardRole;
use App\Service\UrlContextService;
use App\UseCase\v2\BoardRole\UpdateBoardRole;

class BoardRoleUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateBoardRole $updateBoardRole,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /** @var BoardRole $boardRole*/
        $boardRole = $payload->data;
        $id = $boardRole->getId();

        ($this->updateBoardRole)($boardRole);

        $messageKey = 'boardRole.flash.success.edit';
        if (!$id) {
            $messageKey = 'boardRole.flash.success.create';
        }

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
