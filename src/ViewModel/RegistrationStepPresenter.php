<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\RegistrationStep;
use App\Repository\LevelRepository;
use App\Repository\LicenceRepository;
use App\Repository\RegistrationStepRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\ReplaceKeywordsService;
use App\Service\SeasonService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class RegistrationStepPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?RegistrationStep $registrationStep, ?UserViewModel $user, ?int $step, int $render, ?string $class = null): void
    {
        if (null !== $registrationStep) {
            $this->viewModel = RegistrationStepViewModel::fromRegistrationStep(
                $registrationStep,
                $this->services,
                $user,
                $step,
                $render,
                $class
            );
        } else {
            $this->viewModel = new RegistrationStepViewModel();
        }
    }

    public function viewModel(): RegistrationStepViewModel
    {
        return $this->viewModel;
    }
}
