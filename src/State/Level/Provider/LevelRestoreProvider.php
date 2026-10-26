<?php

declare(strict_types=1);

namespace App\State\Level\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\Level;
use App\Mapper\Level\LevelMapper;
use App\Service\SeasonService;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<Level>
 */
class LevelRestoreProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private LevelMapper $levelMapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        return new ListItemUpdateView(
            item: $this->levelMapper->mapToView(
                $data,
                $targetUrl,
                $this->seasonService->getCurrentSeason(),
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
            sortableUrl: $this->urlContextService->generateUrl('admin_level_order', ['level' => 0], $targetUrl),
        );
    }
}
