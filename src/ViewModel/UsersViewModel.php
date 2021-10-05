<?php

namespace App\ViewModel;


class UsersViewModel 
{
    public ?array $users;

    public static function fromUsers(array $users, int $currentSeason): UsersViewModel
    {
        $usersViewModel = [];
        if (!empty($users)) {
            foreach ($users as $user) {
                $usersViewModel[] = UserViewModel::fromUser($user, $$currentSeason);
            }
        }

        $usersView = new self();
        $usersView->users = $usersViewModel;

        return $usersView;
    }
}