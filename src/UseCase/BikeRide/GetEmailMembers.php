<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\Member;
use App\Entity\Session;

class GetEmailMembers
{
    public function execute(BikeRide $bikeRide): string
    {
        $users = $this->getUsers($bikeRide);
        $emails = $this->getEmails($users);

        return (!empty($emails)) ? implode(',', $emails) : 'aucun';
    }

    private function getUsers(BikeRide $bikeRide): array
    {
        $users = [];
        foreach ($bikeRide->getClusters() as $cluster) {
            foreach ($cluster->getSessions() as $session) {
                if (!in_array($session->getAvailability(), [Session::AVAILABILITY_AVAILABLE, Session::AVAILABILITY_UNAVAILABLE])) {
                    $users[] = $session->getMember();
                }
            }
        }

        return $users;
    }

    private function getEmails(array $users): array
    {
        $emails = [];
        /** @var Member $member */
        foreach ($users as $member) {
            $emails[] = $member->getMainIdentity()->getEmail();
        }
        return $emails;
    }
}
