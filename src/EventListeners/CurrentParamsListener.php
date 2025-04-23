<?php

namespace App\EventListeners;

use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentParamsListener
{
    public function __construct(
        private SeasonService $seasonService,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function onKernelRequest(): void
    {
        $session = $this->requestStack->getSession();
        $currentSeason = $this->seasonService->getCurrentSeason();
        $session->set('currentSeason', $currentSeason);

        $databaseName = $this->entityManager->getConnection()->getParams()['dbname'];
        $session->set('databaseName', $databaseName);

        date_default_timezone_set($this->parameterBag->get('timezone'));
    }
}
