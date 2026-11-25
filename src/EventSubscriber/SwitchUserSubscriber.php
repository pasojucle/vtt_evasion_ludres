<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Member;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SwitchUserSubscriber implements EventSubscriberInterface
{
    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $request = $event->getRequest();

        /** @var Member $member */
        $member = $event->getTargetUser();
        $identity = $member->getIdentity();
        $request->getSession()->set('user_fullName', $identity->getFullName());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // constant for security.switch_user
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }
}
