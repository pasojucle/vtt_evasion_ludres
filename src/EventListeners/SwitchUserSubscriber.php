<?php

namespace App\EventListeners;

// use App\Dto\DtoTransformer\UserDtoTransformer;

use App\ViewModel\UserPresenter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SwitchUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPresenter $userPresenter,
    )
    {
        
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $request = $event->getRequest();

        $userDto = $this->userPresenter->present($event->getTargetUser());
        $request->getSession()->set('user_fullName', $this->userPresenter->viewModel()->member->fullName);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // constant for security.switch_user
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }
}