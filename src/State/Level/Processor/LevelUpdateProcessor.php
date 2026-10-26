<?php

declare(strict_types=1);

namespace App\State\Level\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Entity\Level;
use App\Service\UrlContextService;
use App\UseCase\v2\Level\UpdateLevel;

class LevelUpdateProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UpdateLevel $updateLevel,
        private UrlContextService $urlContextService,
    ) {
    }

    /**
     * @param ActionPayload $payload
     */
    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        /** @var Level $level*/
        $level = $payload->data;
        $id = $level->getId();

        ($this->updateLevel)($level);

        $messageKey = 'level.flash.success.edit';
        if (!$id) {
            $messageKey = 'level.flash.success.create';
        }

        return new RedirectProcessorResult(
            success: true,
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            messageKey: $messageKey,
        );
    }
}
