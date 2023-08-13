<?php

namespace App\EventListeners;

// use App\Dto\DtoTransformer\UserDtoTransformer;

use App\Entity\User;
use App\Dto\DtoTransformer\UserDtoTransformer;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SwitchUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer,
    )
    {
        
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $request = $event->getRequest();

        /** @var User $user */
        $user = $event->getTargetUser();
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $request->getSession()->set('user_fullName', $userDto->member->fullName);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // constant for security.switch_user
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }
}