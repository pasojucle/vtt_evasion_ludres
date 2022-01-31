<?php

declare(strict_types=1);

namespace App\ViewModel;

class UsersViewModel
{
    public ?array $users;

    public static function fromUsers(array $users, array $services): UsersViewModel
    {
        $usersViewModel = [];
        if (! empty($users)) {
            foreach ($users as $user) {
                $usersViewModel[] = UserViewModel::fromUser($user, $services);
            }
        }

        $usersView = new self();
        $usersView->users = $usersViewModel;

        return $usersView;
    }
}
