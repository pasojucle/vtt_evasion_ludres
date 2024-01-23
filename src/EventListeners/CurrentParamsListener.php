<?php

namespace App\EventListeners;

use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentParamsListener
{
    public function __construct(
        private SeasonService $seasonService,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function onKernelRequest()
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        $this->requestStack->getSession()->set('currentSeason', $currentSeason);

        $databaseName = $this->entityManager->getConnection()->getParams()['dbname'];
        $this->requestStack->getSession()->set('databaseName', $databaseName);
    }
}
