<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SelfAuthentication
{
    public function __construct(
        private RequestStack $requestStack,
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function authenticate(User $user): void
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), $user->getRoles());
        $this->tokenStorage->setToken($token);

        // $event = new SecurityEvents();
        $event = new InteractiveLoginEvent($this->requestStack->getCurrentRequest(), $this->tokenStorage->getToken());
        $this->eventDispatcher->dispatch($event);
    }

    public function logout(): void
    {
        $logoutEvent = new LogoutEvent($this->requestStack->getCurrentRequest(), $this->tokenStorage->getToken());
        $this->eventDispatcher->dispatch($logoutEvent);
        $this->tokenStorage->setToken(null);
    }
}
