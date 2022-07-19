<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\LevelRepository;
use App\Repository\LicenceRepository;
use App\Repository\RegistrationStepRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class RegistrationService
{
    private ?User $user;

    private int $season;
    private ?Licence $seasonLicence;

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
        private UserPresenter $userPresenter,
        private SessionRepository $sessionRepository
    ) {
        $this->season = $this->seasonService->getCurrentSeason();
    }

    public function getTemplate(int $form): ?string
    {
        if (UserType::FORM_REGISTRATION_DOCUMENT === $form) {
            return null;
        }

        return 'registration/form/' . str_replace('form.', '', UserType::FORMS[$form]) . '.html.twig';
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function getReplaces(User $user)
    {
        $this->userPresenter->present($user);
        $user = $this->userPresenter->viewModel();

        return [
            '{{ prenom_nom }}' => $user->getFullName(),
            '{{ prenom_nom_enfant }}' => $user->getFullNameChildren(),
        ];
    }

    public function isAllreadyRegistered(?User $user): bool
    {
        $isAllreadyRegistered = false;

        if (null !== $user) {
            $licence = $user->getSeasonService($this->season);
            if (null !== $licence) {
                if ($licence->isFinal() && Licence::STATUS_IN_PROCESSING < $licence->getStatus()) {
                    $isAllreadyRegistered = true;
                }
                if (!$licence->isFinal() && 1 > count(${$user}->getSessionsDone())) {
                    $isAllreadyRegistered = true;
                }
            }
        }

        return $isAllreadyRegistered;
    }

    private function getForm(RegistrationStep $registrationStep, bool $isKinship, ?int $category, int $step): ?Form
    {
        $form = null;

        if (null !== $registrationStep->getForm() && UserType::FORM_REGISTRATION_DOCUMENT !== $registrationStep->getForm()) {
            $form = $this->formFactory->create(UserType::class, $this->user, [
                'attr' => [
                    'action' => $this->router->generate('registration_form', [
                        'step' => $step,
                    ]),
                ],
                'current' => $registrationStep,
                'is_kinship' => $isKinship,
                'category' => $category,
                'season_licence' => $this->seasonLicence,
            ]);
        }

        return $form;
    }
}
