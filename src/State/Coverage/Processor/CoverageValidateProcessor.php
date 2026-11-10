<?php

declare(strict_types=1);

namespace App\State\Coverage\Processor;

use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Licence;
use App\Service\UrlContextService;
use Doctrine\ORM\EntityManagerInterface;

class CoverageValidateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(object $entity, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /** @var Licence $entity */
        $entity->setCurrentSeasonForm(true);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: false,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'coverage.flash.success.valided',
            flashType: 'succes',
        );
    }
}
