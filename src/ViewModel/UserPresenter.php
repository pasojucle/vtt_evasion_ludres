<?php

namespace App\ViewModel;

use App\Entity\User;
use App\Entity\OrderHeader;
use App\ViewModel\UserViewModel;
use App\ViewModel\OrderViewModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserPresenter 
{
    private ParameterBagInterface $parameterBag;
    private $viewModel;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function present(?User $user): void
    {
        
        if (null !== $user) {
            $this->viewModel = UserViewModel::fromUser($user);
        } else {
            $this->viewModel = new UserViewModel();
        }
    }


    public function viewModel(): UserViewModel
    {
        return $this->viewModel;
    }

}