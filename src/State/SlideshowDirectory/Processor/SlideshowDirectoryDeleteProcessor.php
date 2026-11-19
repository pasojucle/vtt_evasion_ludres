<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Slideshow\DeleteSlideshowDirectory;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class SlideshowDirectoryDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private DeleteSlideshowDirectory $deleteSlideshowDirectory,
        private UrlContextService $urlContextService,
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $slideshowDirectory = $payload->data;

        ($this->deleteSlideshowDirectory)($slideshowDirectory);

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'slideshow.directory.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
