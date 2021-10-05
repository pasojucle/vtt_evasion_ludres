<?php

namespace App\ViewModel;


use App\Service\LicenceService;


class UsersPresenter 
{
    private LicenceService $licenceService;
    private $viewModel;

    public function __construct(LicenceService $licenceService)
    {
        $this->licenceService = $licenceService;
    }

    public function present(array $users): void
    {
        $currentSeason = $this->licenceService->getCurrentSeason();

        if (!empty($users)) {
            $this->viewModel = UsersViewModel::fromUsers($users, $currentSeason);
        } else {
            $this->viewModel = new UsersViewModel();
        }
    }


    public function viewModel(): UsersViewModel
    {
        return $this->viewModel;
    }

}