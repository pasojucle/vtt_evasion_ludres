<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Session;

class SessionWarningToggle
{
    public function __construct(
        public Session $session,
        public ?string $messageId = null,
    ) {
    }
}
