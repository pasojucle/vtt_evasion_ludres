<?php

namespace App\EventListeners;

use App\Service\SeasonService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CurrentSeasonListener
{
    public function __construct(
        private SeasonService $seasonService,
        private RequestStack $requestStack
    ) {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $this->requestStack->getSession()->set('currentSeason', $currentSeason);
    }
}
