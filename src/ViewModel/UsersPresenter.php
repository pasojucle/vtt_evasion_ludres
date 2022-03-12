<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class UsersPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array|Paginator $users): void
    {
        if (!empty($users)) {
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
