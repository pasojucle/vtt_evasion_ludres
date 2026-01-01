<?php

namespace App\EventListeners;

use Throwable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentParamsListener
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function onKernelRequest(): void
    {
        $databaseName = $this->entityManager->getConnection()->getParams()['dbname'];
        $this->requestStack->getSession()->set('databaseName', $databaseName);

        date_default_timezone_set($this->parameterBag->get('timezone'));
    }
}
