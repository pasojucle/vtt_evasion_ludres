<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class UsersViewModel
{
    public ?array $users;

    public static function fromUsers(array|Paginator $users, ServicesPresenter $services): UsersViewModel
    {
        $usersViewModel = [];
        if (!empty($users)) {
            foreach ($users as $user) {
                $usersViewModel[] = UserViewModel::fromUser($user, $services);
            }
        }

        $usersView = new self();
        $usersView->users = $usersViewModel;

        return $usersView;
    }
}
