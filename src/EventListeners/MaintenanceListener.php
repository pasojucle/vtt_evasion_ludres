<?php

declare(strict_types=1);

namespace App\EventListeners;

use App\Service\BackgroundService;
use App\Service\ParameterService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceListener
{
    private bool $maintenanceMode;
    private array $ipAuthorized;

    public function __construct(
        array $maintenance,
        private Environment $environment,
        private ParameterService $parameterService,
        private BackgroundService $backgroundService,
        private RequestStack $request,
    ) {
        $this->maintenanceMode = $this->parameterService->getParameterByName('MAINTENANCE_MODE');
        $this->ipAuthorized = $maintenance['ipAuthorized'];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // This will get the value of our maintenance parameter
        $maintenance = $this->maintenanceMode;
        $remoteAddr = $this->request->getCurrentRequest()->server->get('REMOTE_ADDR');
        $currentIP = $remoteAddr;
        // This will detect if we are in dev environment (app_dev.php)
        // $debug = in_array($this->container->get('kernel')->getEnvironment(), ['dev']);
        // If maintenance is active and in prod environment
        if ($maintenance and !in_array($currentIP, $this->ipAuthorized, true)) {
            // We load our maintenance template

            $template = $this->environment->render('maintenance/maintenance.html.twig', [
                'default_background' => $this->backgroundService->getDefault(),
                'address_ip' => $remoteAddr,
            ]);
            // We send our response with a 503 response code (service unavailable)
            $event->setResponse(new Response($template, 503));
            $event->stopPropagation();
        }
    }
}
