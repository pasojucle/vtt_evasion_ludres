<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\Session;
use App\ViewModel\UsersPresenter;

class GetEmailMembers
{
    public function __construct(private UsersPresenter $usersPresenter)
    {
    }

    public function execute(BikeRide $bikeRide): string
    {
        $this->getUsers($bikeRide);
        $emails = $this->getEmails();

        return (!empty($emails)) ? implode(',', $emails) : 'aucun';
    }

    private function getUsers(BikeRide $bikeRide): void
    {
        $users = [];
        foreach ($bikeRide->getClusters() as $cluster) {
            foreach ($cluster->getSessions() as $session) {
                if ( $session->getAvailability() < Session::AVAILABILITY_AVAILABLE) {
                    $users[] = $session->getUser();
                }
            }
        }

        
        $this->usersPresenter->present($users);
    }

    private function getEmails(): array
    {
        $emails = [];
        foreach ($this->usersPresenter->viewModel()->users as $user) {
            $emails[] = $user->mainEmail;
        }
        return $emails;
    }
}
