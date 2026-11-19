<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\Survey;
use App\Mapper\Survey\SurveyMapper;
use App\Service\UrlContextService;

/**
 * @implements TurboStreamProviderInterface<Survey>
 */
class SurveyToggleProvider implements TurboStreamProviderInterface
{
    public function __construct(
        private SurveyMapper $SurveyMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        return new ListItemUpdateView(
            item: $this->SurveyMapper->mapToView(
                $data,
                $this->urlContextService->decodeUrl($context->encodedFallback),
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
