<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Dto\View\ListItemUpdateView;
use App\Entity\BikeRide;
use App\Mapper\Activity\ActivityMapper;
use App\Repository\SessionRepository;
use App\Service\UrlContextService;
use App\State\Activity\Trait\ActivityListDataTrait;

/**
 * @implements TurboStreamProviderInterface<BikeRide>
 */
class ActivityRestoreProvider implements TurboStreamProviderInterface
{
    use ActivityListDataTrait;

    public function __construct(
        private SessionRepository $sessionRepository,
        private UrlContextService $urlContextService,
        private ActivityMapper $activityMapper,
    ) {
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ListItemUpdateView
    {
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        return new ListItemUpdateView(
            item: $this->activityMapper->mapToView(
                $data,
                $this->getParticipantTotalByActivity([$data->getId()]),
                $targetUrl,
            ),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
