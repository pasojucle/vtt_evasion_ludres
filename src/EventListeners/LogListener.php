<?php

declare(strict_types=1);

namespace App\EventListeners;

use App\Entity\User;
use App\Service\LogService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LogListener
{
    public function __construct(
        private readonly LogService $logService,
        private Security $security,
    ) {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $route = $event->getRequest()->get('_route');
        $routes = ['club_summary', 'slideshow_images',  'second_hand_list'];
        
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user && in_array($route, $routes)) {
            $this->logService->WriteByRoute($route, $user);
        }
    }
}
