<?php

declare(strict_types=1);

namespace App\State\Product\Processor;

use App\Dto\Payload\ProductToggleDto;
use App\Dto\State\ComponentProcessorResult;
use App\Mapper\Product\ProductStatusMapper;
use App\Service\CsrfTokenService;
use App\Service\DisableService;
use App\State\Interface\ComponentProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ProductToggleProcessor implements ComponentProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private CsrfTokenService $csrfTokenService,
        private DisableService $disableService,
        private ProductStatusMapper $productStatusMapper,
    ) {
    }
    
    /**
     * @implements ComponentProcessorInterface<ProductToggleDto>
     */
    public function process(object $entity): ComponentProcessorResult
    {
        $tokenId = $this->csrfTokenService->getTokenId($entity->product);
        $csrfToken = new CsrfToken($tokenId, $entity->token);

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            new ComponentProcessorResult(
                success: false,
                messageKey: 'Jeton CSRF invalide.',
                flashType: 'danger',
            );
        }

        $product = $entity->product;
        $this->disableService->toggle($product);

        $this->entityManager->flush();

        return new ComponentProcessorResult(
            success: true,
            messageKey: $product->isDisabled()
                ? 'product.flash.success.disabled'
                : 'product.flash.success.enabled',
            flashType: 'success',
            component: $this->productStatusMapper->mapToView($product, $tokenId)
        );
    }
}
