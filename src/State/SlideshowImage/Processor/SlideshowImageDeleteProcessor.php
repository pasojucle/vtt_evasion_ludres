<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Processor;

use App\Core\Contract\PayloadInterface;
use App\Core\Contract\Processor\RedirectProcessorInterface;
use App\Core\Dto\ActionPayload;
use App\Core\Dto\HandlerContext;
use App\Core\Dto\RedirectProcessorResult;
use App\Service\UrlContextService;
use App\UseCase\v2\Slideshow\DeleteSlideshowImage;

/**
 * @implements RedirectProcessorInterface<ActionPayload>
 */
class SlideshowImageDeleteProcessor implements RedirectProcessorInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private DeleteSlideshowImage $deleteSlideshowImage
    ) {
    }

    public function process(PayloadInterface $payload, ?HandlerContext $context = null): RedirectProcessorResult
    {
        $slideshowImage = $payload->data;

        ($this->deleteSlideshowImage)($slideshowImage);
    
        return new RedirectProcessorResult(
            success: true,
            messageKey: 'slideshow.image.flash.success.delete',
            targetUrl: $this->urlContextService->decodeUrl($context->encodedFallback),
            flashType: 'success'
        );
    }
}
