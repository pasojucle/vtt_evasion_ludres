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

class RegistrationStepPresenter
{
    private $viewModel;
    private int $season;

    public function __construct(
        private RegistrationStepRepository $registrationStepRepository,
        private Security $security,
        private UrlGeneratorInterface $router,
        private FormFactoryInterface $formFactory,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private LicenceRepository $licenceRepository,
        private SeasonService $seasonService,
        private LevelRepository $levelRepository,
        private UserService $userService,
        private SessionRepository $sessionRepository,
        private ReplaceKeywordsService $replaceKeywordsService
    ) {
        $this->season = $this->seasonService->getCurrentSeason();
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
            'licenceRepository' => $this->licenceRepository,
            'seasonService' => $this->seasonService,
            'levelRepository' => $this->levelRepository,
            'sessionRepository' => $this->sessionRepository,
            'replaceKeywordsService' => $this->replaceKeywordsService,
        ];
    }
}
