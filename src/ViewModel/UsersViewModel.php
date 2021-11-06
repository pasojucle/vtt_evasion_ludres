<?php

namespace App\ViewModel;

use App\Service\LicenceService;

class UsersViewModel 
{
    public ?array $users;

    public static function fromUsers(array $users, array $data): UsersViewModel
    {
        $usersViewModel = [];
        if (!empty($users)) {
            foreach ($users as $user) {
                $usersViewModel[] = UserViewModel::fromUser($user, $data);
            }
        }

        $usersView = new self();
        $usersView->users = $usersViewModel;

        return $usersView;
    }
}