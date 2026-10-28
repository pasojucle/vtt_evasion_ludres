<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\JsonProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\JsonProcessorResult;
use App\Dto\Payload\SortablePayload;
use App\Service\CsrfTokenService;
use App\UseCase\v2\Level\OrderLevel;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements JsonProcessorInterface<ActionDirectPayload>
 */
class LevelOrderProcessor implements JsonProcessorInterface
{
    public function __construct(
        private OrderLevel $orderLevel,
        private CsrfTokenService $csrfTokenService,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): JsonProcessorResult
    {
        /**
         * @param SortablePayload $data
         */
        $data = $payload->data;
        $level = $data->entity;

        $tokenId = $this->csrfTokenService->getTokenId($level);
        $csrfToken = new CsrfToken($tokenId, $payload->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            return new JsonProcessorResult(
                success: false,
                data: [
                    'message' => 'Jeton CSRF invalide.',
                ]
            );
        }

        ($this->orderLevel)($level, $data->newOrder);

        return new JsonProcessorResult(
            success: true,
        );
    }
}
