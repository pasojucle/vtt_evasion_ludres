<?php

namespace App\ViewModel;

use App\Entity\User;
use App\Entity\Licence;
use App\Service\UserService;
use App\Service\LicenceService;
use App\Entity\RegistrationStep;
use App\Repository\UserRepository;
use App\Repository\LevelRepository;
use App\Repository\LicenceRepository;
use App\Repository\SessionRepository;
use App\Service\ReplaceKeywordsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\RegistrationStepRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationStepPresenter
{
    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private Security $security,
        private UrlGeneratorInterface $router,
        private FormFactoryInterface $formFactory,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private LicenceRepository $licenceRepository,
        private LicenceService $licenceService,
        private LevelRepository $levelRepository,
        private UserService $userService,
        private SessionRepository $sessionRepository,
        private ReplaceKeywordsService $replaceKeywordsService
    )
    {
        $this->season = $this->licenceService->getCurrentSeason();
    }

    private function getServices(): array
    {
        return [
            'registrationStepRepository' => $this->registrationStepRepository,
            'security' => $this->security,
            'router' => $this->router,
            'formFactory' => $this->formFactory,
            'requestStack' => $this->requestStack,
            'entityManager' => $this->entityManager,
            'userRepository' => $this->userRepository,
            'entityManager' => $this->entityManager,
            'licenceRepository' => $this->licenceRepository,
            'licenceService' => $this->licenceService,
            'levelRepository' => $this->levelRepository,
            'sessionRepository' => $this->sessionRepository,
            'replaceKeywordsService' => $this->replaceKeywordsService,
        ];
    }

    public function present(?RegistrationStep $registrationStep, ?UserViewModel $user, ?int $step, int $render, ?string $class = null): void
    {
        if (null !== $registrationStep) {
            $this->viewModel = RegistrationStepViewModel::fromRegistrationStep(
                $registrationStep,
                $this->getServices(),
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