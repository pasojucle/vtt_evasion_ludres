<?php

namespace App\Service;

use DateTime;
use App\Entity\User;
use App\DataTransferObject\User as UserDto;
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
use App\Repository\LicenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\RegistrationStepRepository;
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

    public function __construct(
        RegistrationStepRepository $registrationStepRepository,
        Security $security,
        UrlGeneratorInterface $router,
        FormFactoryInterface $formFactory,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        LicenceRepository $licenceRepository,
        LicenceService $licenceService
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

        $category = $this->seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $this->seasonLicence->isFinal());
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
            $idMax = $this->userRepository->findMaxId();
            ++$idMax;
            $this->user->setLicenceNumber('VTTEVASIONLUDRES'.$idMax)
                ->setRoles(['ROLE_USER']);
            $this->entityManager->persist($this->user);
        } 
        $this->seasonLicence = $this->user->getSeasonLicence($this->season);
        if (null === $this->seasonLicence) {
            $this->seasonLicence = new Licence();
            $this->seasonLicence->setSeason($this->season);
            if (!$this->user->getLicences()->isEmpty()) {
                $this->seasonLicence->setFinal(true)
                    ->setType(Licence::TYPE_HIKE);
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
            foreach (range(0, 8) as $number) {
                $healthQuestion = new HealthQuestion();
                $healthQuestion->setField($number);
                $health->addHealthQuestion($healthQuestion);
                $this->entityManager->persist($healthQuestion);
            }
            $this->entityManager->persist($health);
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
                $identity = new Identity();
                $identity->setKinship(Identity::KINSHIP_FATHER);
                $this->user->addIdentity($identity);
                $this->entityManager->persist($identity);
            }
            if ($this->user->getIdentities()->count() === 2 && null === $this->user->getIdentities()->last()->getAddress()) {
                $address = new Address();
                $this->entityManager->persist($address);
                $this->user->getIdentities()->last()->setAddress($address);
                $this->entityManager->persist($this->user->getIdentities()->last());
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
            $this->seasonLicence->setType(Licence::TYPE_HIKE);
        }
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function getReplaces(User $user)
    {
        /**@var UserDto $userDto */
        $user = new UserDto($user);

        return [
            '{{ prenom_nom }}' => $user->getFullName(),
            '{{ prenom_nom_enfant }}' => $user->getFullNameChildren(),
        ];
    }
}