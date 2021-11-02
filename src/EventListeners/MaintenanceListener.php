<?php
namespace App\EventListeners;

use Twig\Environment;
use App\Service\ParameterService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class MaintenanceListener
{
    private Environment $environment;
    private ParameterService $parameterService;
    public function __construct($maintenance, Environment $environment, ParameterService $parameterService)
    {
        $this->environment = $environment;
        $this->parameterService = $parameterService;
        $this->maintenance = $this->parameterService->getParameterByName('MAINTENANCE_MODE');
        $this->ipAuthorized = $maintenance["ipAuthorized"];
    }
    public function onKernelRequest(RequestEvent $event)
    {
        // This will get the value of our maintenance parameter
        $maintenance = $this->maintenance;
        $currentIP = $_SERVER['REMOTE_ADDR'];
        // This will detect if we are in dev environment (app_dev.php)
        // $debug = in_array($this->container->get('kernel')->getEnvironment(), ['dev']);
        // If maintenance is active and in prod environment
        if ($maintenance AND !in_array($currentIP, $this->ipAuthorized)) {
            // We load our maintenance template
;
            $template = $this->environment->render('maintenance/maintenance.html.twig');
            // We send our response with a 503 response code (service unavailable)
            $event->setResponse(new Response($template, 503));
            $event->stopPropagation();
        }
    }
} 