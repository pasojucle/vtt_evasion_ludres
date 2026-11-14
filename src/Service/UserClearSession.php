<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class UserClearSession
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function execute(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('user_fullName');
    }
}
