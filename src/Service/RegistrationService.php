<?php

namespace App\Service;

use DateTime;
use App\Entity\User;
use App\Entity\Level;
use App\Entity\Health;
use App\Form\UserType;
use App\Entity\Address;
use App\Entity\Disease;
use App\Entity\Licence;
use App\Entity\Approval;
use App\Entity\Identity;
use App\Entity\HealthQuestion;
use App\Entity\RegistrationStep;
use Symfony\Component\Form\Form;
use App\Repository\UserRepository;
use App\Repository\LevelRepository;
use App\Repository\LicenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\DataTransferObject\User as UserDto;
use Symfony\Component\Security\Core\Security;
use App\Repository\RegistrationStepRepository;
use App\Repository\SessionRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationService
{
    private ?User $user;
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
        private LicenceService $licenceService,
        private LevelRepository $levelRepository,
        private UserService $userService,
        private SessionRepository $sessionRepository
    )
    {
        $this->season = $this->licenceService->getCurrentSeason();
    }

    

    private function getForm(RegistrationStep $registrationStep, bool $isKinship, ?int $category, int $step): ?Form
    {
        $form = null;
        
        if (null !== $registrationStep->getForm() && UserType::FORM_REGISTRATION_DOCUMENT !== $registrationStep->getForm()) {
            $form = $this->formFactory->create(UserType::class, $this->user, [
                'attr' =>[
                    'action' => $this->router->generate('registration_form', ['step' => $step]),
                ],
                'current' => $registrationStep,
                'is_kinship' => $isKinship,
                'category' => $category,
                'season_licence' => $this->seasonLicence,
            ]);
        }

        return $form;
    }

    public function getTemplate(int $form): ?string
    {
        if (UserType::FORM_REGISTRATION_DOCUMENT === $form) {
            return null;
        }
        return 'registration/form/'. str_replace('form.','', UserType::FORMS[$form]).'.html.twig';
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function getReplaces(User $user)
    {
        /**@var UserDto $userDto */
        $user = $this->userService->convertToUser($user);

        return [
            '{{ prenom_nom }}' => $user->getFullName(),
            '{{ prenom_nom_enfant }}' => $user->getFullNameChildren(),
        ];
    }

    public function isAllreadyRegistered(?User $user): bool
    {
        $isAllreadyRegistered = false;

        if (null !== $user) {
            $licence = $user->getSeasonLicence($this->season);
            if (null !== $licence) {
                if ($licence->isFinal() && Licence::STATUS_IN_PROCESSING < $licence->getStatus()) {
                    $isAllreadyRegistered = true;
                }
                if (!$licence->isFinal() && 1 > count($$user->getSessionsDone())) {
                    $isAllreadyRegistered = true;
                }
            }
        }
        
        return $isAllreadyRegistered;
    }
}