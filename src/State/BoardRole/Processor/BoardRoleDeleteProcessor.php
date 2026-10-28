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
use App\UseCase\v2\BoardRole\DeleteBoardRole;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class BoardRoleDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private DeleteBoardRole $deleteBoardRole,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /**
         * @var BoardRole $boardRole
         */
        $boardRole = $payload->data;
        ($this->deleteBoardRole)($boardRole);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'board_role.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
