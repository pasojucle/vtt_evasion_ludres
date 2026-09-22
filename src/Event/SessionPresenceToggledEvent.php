<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Session;
use Symfony\Contracts\EventDispatcher\Event;

class SessionPresenceToggledEvent extends Event
{
    public function __construct(
        public readonly Session $session,
    ) {
    }
}