<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Model\Currency;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SessionsViewModel
{
    public ?array $sessions = [];

    public static function fromSessions(array|Paginator $sessions, ServicesPresenter $services): SessionsViewModel
    {
        $sessionsViewModel = [];
        if (!empty($sessions)) {
            foreach ($sessions as $session) {
                $sessionsViewModel[] = SessionViewModel::fromSession($session, $services);
            }
        }

        $sessionsView = new self();
        $sessionsView->sessions = $sessionsViewModel;

        return $sessionsView;
    }
}
