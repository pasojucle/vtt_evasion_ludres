<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class NavigationSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !$request->isMethod('GET')) {
            return;
        }

        $route = $request->attributes->get('_route');
        if (!$route) {
            return;
        }

        if (1 === preg_match('/admin_([a-zA-Z0-9]+)_(list|index)/', $route, $matches)) {
            $context = $matches[1];
            
            $request->getSession()->set("last_url_{$context}", $request->getUri());
            $request->getSession()->set("last_list_url", $request->getUri());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
