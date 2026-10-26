<?php

declare(strict_types=1);

namespace App\State\Skill\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\Skill;
use App\Mapper\Skill\SkillMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<Skill>
 */
class SkillRestoreProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private UrlContextService $urlContextService,
        private SkillMapper $skillMapper,
    ) {
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        return new ListItemUpdateView(
            item: $this->skillMapper->mapToView(
                $data,
                $targetUrl,
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
