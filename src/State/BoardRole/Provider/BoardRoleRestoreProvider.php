<?php

declare(strict_types=1);

namespace App\State\BoardRole\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\BoardRole;
use App\Mapper\BoardRole\BoardRoleMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<BoardRole>
 */
class BoardRoleRestoreProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private BoardRoleMapper $boardRoleMapper,
    ) {
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        return new ListItemUpdateView(
            item: $this->boardRoleMapper->mapToView(
                $data,
                $targetUrl,
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
            sortableUrl: $this->urlContextService->generateUrl('admin_board_role_order', ['boardRole' => 0], $targetUrl),
        );
    }
}
