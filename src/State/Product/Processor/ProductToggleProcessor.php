<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\TurboStreamProcessorInterface;
use App\Core\Dto\ActionDirectPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\TurboStreamProcessorResult;
use App\Service\CsrfTokenService;
use App\UseCase\v2\Product\ToggleProduct;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @implements TurboStreamProcessorInterface<ActionDirectPayload>
 */
class ProductToggleProcessor implements TurboStreamProcessorInterface
{
    public function __construct(
        private ToggleProduct $toggleProduct,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): TurboStreamProcessorResult
    {
        $product = $payload->data;
        $tokenId = $this->csrfTokenService->getTokenId($product);
        $csrfToken = new CsrfToken($tokenId, $payload->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            new TurboStreamProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
            );
        }

        ($this->toggleProduct)($product);

        return new TurboStreamProcessorResult(
            success: true,
            messageKey: $product->isDisabled()
                ? 'product.flash.success.disabled'
                : 'product.flash.success.enabled',
            flashType: 'success',
            data: $product
        );
    }
}
