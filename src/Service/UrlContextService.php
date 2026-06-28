<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlContextService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }


    public function generateUrl(string $route, array $params, $referer): string
    {
        $params['_redirect_to'] = $referer;

        return $this->urlGenerator->generate($route, $params);
    }


    public function generateTargetUrl(string $currentRoute, array $filter = []): string
    {
        return rawurlencode($this->urlGenerator->generate($currentRoute, $filter));
    }


    public function getRedirectUrl(Request $request, string $fallbackRoute = 'admin_dashboard'): string
    {
        $redirectTo = $request->query->get('_redirect_to') ?? $request->request->get('_redirect_to');

        if ($redirectTo) {
            return rawurldecode($redirectTo);
        }

        return $this->urlGenerator->generate($fallbackRoute);
    }
}
