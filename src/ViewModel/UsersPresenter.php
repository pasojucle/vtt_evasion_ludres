<?php

declare(strict_types=1);

namespace App\ViewModel;

class UsersPresenter extends AbstractPresenter
{
    public function present(array $users): void
    {
        if (! empty($users)) {
            $this->viewModel = UsersViewModel::fromUsers($users, $this->services);
        } else {
            $this->viewModel = new UsersViewModel();
        }
    }

    public function viewModel(): UsersViewModel
    {
        return $this->viewModel;
    }
}
