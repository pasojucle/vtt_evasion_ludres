<?php

namespace App\ViewModel;

use App\Entity\User;
use App\Entity\OrderHeader;
use App\Service\LicenceService;
use App\ViewModel\UserViewModel;
use App\ViewModel\OrderViewModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserPresenter 
{
    private ParameterBagInterface $parameterBag;
    private LicenceService $licenceService;
    private $viewModel;

    public function __construct(ParameterBagInterface $parameterBag, LicenceService $licenceService)
    {
        $this->parameterBag = $parameterBag;
        $this->licenceService = $licenceService;
    }

    public function present(?User $user): void
    {
        
        if (null !== $user) {
            $this->viewModel = UserViewModel::fromUser($user, $this->licenceService->getCurrentSeason());
        } else {
            $this->viewModel = new UserViewModel();
        }
    }


    public function viewModel(): UserViewModel
    {
        return $this->viewModel;
    }

}