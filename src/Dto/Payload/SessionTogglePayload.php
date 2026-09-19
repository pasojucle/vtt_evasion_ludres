<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Session;

class SessionTogglePayload
{
    public function __construct(
        public Session $session,
        public ?string $token
    ) {
    }
}
