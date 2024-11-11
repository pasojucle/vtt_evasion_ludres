<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class RouterService
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
    ) {
    }

    public function getRouteInfos(): array
    {
        $referer = $this->requestStack->getCurrentRequest()->headers->get('referer');
        $refererPathInfo = Request::create($referer)->getPathInfo();
        try {
            $routeInfos = $this->router->match($refererPathInfo);
        } catch (Exception) {
            $routeInfos = ['_route' => null];
        }
        return $routeInfos;
    }
}
