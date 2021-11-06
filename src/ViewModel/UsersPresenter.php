<?php

namespace App\ViewModel;


use App\Service\LicenceService;


class UsersPresenter extends AbstractPresenter
{
    public function present(array $users): void
    {
        if (!empty($users)) {
            $this->viewModel = UsersViewModel::fromUsers($users, $this->data);
        } else {
            $this->viewModel = new UsersViewModel();
        }
    }


    public function viewModel(): UsersViewModel
    {
        return $this->viewModel;
    }

}