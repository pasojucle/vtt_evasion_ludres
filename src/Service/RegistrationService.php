<?php

namespace App\Service;

use DateTime;
use App\Entity\User;
use App\Entity\Health;
use App\Form\UserType;
use App\Entity\Licence;
use App\Entity\Approval;
use App\Entity\Identity;
use App\Entity\HealthQuestion;
use App\Entity\RegistrationStep;
use App\Repository\LicenceRepository;
use Symfony\Component\Form\Form;
use App\Repository\UserRepository;
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
        $this->setUser();
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

        $category = $this->seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndTesting($category, $this->seasonLicence->isTesting());
        $stepIndex = $step -1;

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

    private function getForm(RegistrationStep $registrationStep, bool $isKinshiph, ?int $category, int $step): ?Form
    {
        $form = null;
        
        dump($this->user);
        if (null !== $registrationStep->getForm()) {
            $form = $this->formFactory->create(UserType::class, $this->user, [
                'attr' =>[
                    'action' => $this->router->generate('registration_form_validate', ['step' => $step]),
                ],
                'current' => $registrationStep,
                'is_kinship' => $isKinshiph,
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
                ->setRoles(['USER']);
            $this->entityManager->persist($this->user);
        } 
        $this->seasonLicence = $this->user->getSeasonLicence($this->season);
        if (null === $this->seasonLicence) {
            $this->seasonLicence = new Licence();
            $this->seasonLicence->setSeason($this->season);
            if ($this->user->getLicences()->isEmpty()) {
                $this->seasonLicence->setTesting(true);
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
            if ($this->user->getApprovals()->count() < 2) {
                $aproval = new Approval();
                $aproval->setType(User::APPROVAL_GOING_HOME_ALONE);
                $this->user->addApproval($aproval);
                $this->entityManager->persist($aproval);
            }
        }
    }

    public function getSeason(): int
    {
        return $this->season;
    }
}