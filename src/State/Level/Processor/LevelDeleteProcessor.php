<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Level;
use App\Repository\LevelRepository;
use App\Service\OrderByService;
use App\Service\SoftDeleteService;
use App\Service\UrlContextService;
use App\UseCase\v2\Level\DeleteLevel;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class LevelDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private DeleteLevel $deleteLevel,
        private UrlContextService $urlContextService,
    ) {
    }


    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /**
         * @var Level $level
         */
        $level = $payload->data;
        ($this->deleteLevel)($level);

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: 'level.flash.success.delete',
        );
    }
}
