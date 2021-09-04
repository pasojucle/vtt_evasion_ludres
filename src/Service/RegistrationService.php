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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationService
{
    private RegistrationStepRepository $registrationStepRepository;
    private UrlGeneratorInterface $router;
    private FormFactoryInterface $formFactory;
    private SessionInterface $session;
    private ?User $user;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private int $season;
    private LicenceRepository $licenceRepository;
    private ?Licence $seasonLicence;
    private LicenceService $licenceService;
    private LevelRepository $levelRepository;
    private UserService $userService;
    private SessionRepository $sessionRepository;

    public function __construct(
        RegistrationStepRepository $registrationStepRepository,
        Security $security,
        UrlGeneratorInterface $router,
        FormFactoryInterface $formFactory,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        LicenceRepository $licenceRepository,
        LicenceService $licenceService,
        LevelRepository $levelRepository,
        UserService $userService,
        SessionRepository $sessionRepository
    )
    {
        $this->registrationStepRepository = $registrationStepRepository;
        $this->user = $security->getUser();
        $this->seasonLicence = null;
        $this->licenceService = $licenceService;
        $this->season = $this->licenceService->getCurrentSeason();
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->licenceRepository = $licenceRepository;
        $this->levelRepository = $levelRepository;
        $this->userService = $userService;
        $this->sessionRepository = $sessionRepository;
    }

    public function getProgress(int $step)
    {
        $progress = [];
        $progress['prev'] = null;
        $progress['next'] = null;
        $progress['form'] = null;
        $progress['current'] = null;
        $progress['steps'] = null;
        $isKinship = false;

        $this->setUser();
        $this->updateStatus($this->user);
        $category = $this->seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $this->seasonLicence->isFinal(), RegistrationStep::RENDER_VIEW);
        $stepIndex = $step -1;
        
        $progress['max_step'] = count($steps);

        foreach($steps as $key => $registrationStep) {
            if ($key < $stepIndex) {
                $registrationStep->setClass('is-done');
                $progress['prev'] = $key+1;
            } elseif ($key === $stepIndex) {
                $registrationStep->setClass('current');
                $progress['current'] = $registrationStep;
            } else {
                if (null === $progress['next']) {
                    $progress['next'] = $key+1;
                }
            }
            $progress['steps'][$key+1] = $registrationStep;
        }

        if (null !== $progress['prev'] && $steps[$progress['prev']-1]->getForm() === UserType::FORM_IDENTITY) {
            $isKinship = true;
        }

        $progress['form'] = $this->getForm($progress['current'], $isKinship, $category, $step);
        $progress['user'] = $this->user;
        $progress['seasonLicence'] = $this->seasonLicence;

        return $progress;
    }

    private function getForm(RegistrationStep $registrationStep, bool $isKinship, ?int $category, int $step): ?Form
    {
        $form = null;
        
        if (null !== $registrationStep->getForm()) {
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

    public function setUser()
    {
        if (null === $this->user) {
            $this->user = new User();

            $this->user->setRoles(['ROLE_USER']);
            $this->entityManager->persist($this->user);
        } 
        $this->seasonLicence = $this->user->getSeasonLicence($this->season);
        if (null === $this->seasonLicence) {
            $this->seasonLicence = new Licence();
            $this->seasonLicence->setSeason($this->season);
            if (!$this->user->getLicences()->isEmpty()) {
                $this->seasonLicence->setFinal(true)
                    ->setType(Licence::TYPE_HIKE);
            } else {
                $this->seasonLicence->setFinal(false)
                    ->setType(Licence::TYPE_HIKE)
                    ->setCoverage(Licence::COVERAGE_MINI_GEAR);
            }
            if (!$this->user->getIdentities()->isEmpty()) {
                $category = $this->licenceService->getCategory($this->user);
                $this->seasonLicence->setCategory($category);
            }
            $this->entityManager->persist($this->seasonLicence);
            $this->user->addLicence($this->seasonLicence);
        }
        if (null === $this->user->getHealth()) {
            $health = new Health();
            $this->user->setHealth($health);
            $this->entityManager->persist($health);
        }
        if ($this->user->getHealth()->getHealthQuestions()->isEmpty()) {
            foreach (range(0, 8) as $number) {
                $healthQuestion = new HealthQuestion();
                $healthQuestion->setField($number);
                $this->user->getHealth()->addHealthQuestion($healthQuestion);
                $this->entityManager->persist($healthQuestion);
            }
        }
        if ($this->user->getIdentities()->isEmpty()) {
            $identity = new Identity();
            $this->user->addIdentity($identity);
            $address = new Address();
            $this->entityManager->persist($address);
            $identity->setAddress($address);
            $this->entityManager->persist($identity);
        }

        if ($this->user->getApprovals()->isEmpty()) {
            $aproval = new Approval();
            $aproval->setType(User::APPROVAL_RIGHT_TO_THE_IMAGE);
            $this->user->addApproval($aproval);
            $this->entityManager->persist($aproval);
        }

        if (Licence::CATEGORY_MINOR === $this->seasonLicence->getCategory()) {
            if ($this->user->getIdentities()->count() < 2) {
                foreach([Identity::KINSHIP_FATHER, Identity::KINSHIP_MOTHER] as $kinShip) {
                    $identity = new Identity();
                    $identity->setKinship($kinShip);
                    $this->user->addIdentity($identity);
                    $this->entityManager->persist($identity);
                }
            }
            if (isset($identity) && $this->user->getIdentities()->count() > 1 && null === $identity->getAddress()) {
                $address = new Address();
                $this->entityManager->persist($address);
                $identity->setAddress($address);
                $this->entityManager->persist($identity);
            }
            if ($this->user->getApprovals()->count() < 2) {
                $aproval = new Approval();
                $aproval->setType(User::APPROVAL_GOING_HOME_ALONE);
                $this->user->addApproval($aproval);
                $this->entityManager->persist($aproval);
            }
            if ($this->user->getHealth()->getDiseases()->isEmpty()) {
                foreach (array_keys(Disease::LABELS) as $label) {
                    $type = Disease::TYPE_DISEASE;
                    if (Disease::LABEL_OTHER < $label) {
                        $type = Disease::TYPE_ALLERGY;
                    }
                    if (Disease::LABEL_POLLEN_BEES < $label) {
                        $type = Disease::TYPE_INTOLERANCE;
                    }
                    $disease = new Disease();
                    $disease->setType($type)
                        ->setLabel($label);
                    $this->entityManager->persist($disease);
                    $this->user->getHealth()->addDisease($disease);
                }
            }
            $awaitingEvaluationlevel = $this->levelRepository->findAwaitingEvaluation();
            $this->seasonLicence->setType(Licence::TYPE_HIKE);
            $this->user->setLevel($awaitingEvaluationlevel);
        } else {
            $approvalsGoingHomeAlone = $this->user->getApprovalsGoingHomeAlone();
            if (!$approvalsGoingHomeAlone->isEmpty()) {
                foreach ($approvalsGoingHomeAlone as $approval) {
                    $this->user->removeApproval($approval);
                    $this->entityManager->remove($approval);
                }
            }
            $this->user->setLevel(null);
        }
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

    public function updateStatus(): void
    {
        $licence = $this->seasonLicence;
        if (!$licence->isFinal() && 
            ((0 < count($this->user->getDoneSessions()) && $licence->getCategory() === Licence::CATEGORY_MINOR)
            || (0 < count($this->user->getSessions()) && $licence->getCategory() === Licence::CATEGORY_ADULT))) {
            $this->seasonLicence->setFinal(true)
                ->setStatus(Licence::STATUS_IN_PROCESSING);
        }
    }
}