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

    public function __construct(
        RegistrationStepRepository $registrationStepRepository,
        Security $security,
        UrlGeneratorInterface $router,
        FormFactoryInterface $formFactory,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    )
    {
        $this->registrationStepRepository = $registrationStepRepository;
        $this->user = $security->getUser();
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function getProgress($type, $step)
    {
        $progress = [];
        $progress['prev'] = null;
        $progress['next'] = null;
        $progress['form'] = null;
        $progress['current'] = null;
        $progress['steps'] = null;

        if (!empty($step)) {
            $steps = $this->registrationStepRepository->findByType($type);
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
            $progress['form'] = $this->getForm($progress['current'], $type, $step);
            
        }
        $progress['user'] = $this->user;
        return $progress;
    }

    private function getForm($registrationStep, $type, $step): ?Form
    {
        $form = null;
        if (null === $this->user) {
            $this->user = new User();
            $idMax = $this->userRepository->findMaxId();
            ++$idMax;
            $this->user->setLicenceNumber('VTTEVASIONLUDRES'.$idMax)
                ->setRoles(['USER']);
            $this->entityManager->persist($this->user);
        } else {
            if (null !== $this->user->getLicence()) {
                $this->user->getLicence()->setNewMember(false);
            }
        }
        if (null === $this->user->getHealth()) {
            $health = new Health();
            $this->user->setHealth($health);

            if ($this->session->get('healthQuestions')) {
                foreach ($this->session->get('healthQuestions') as $healthQuestion) {
                    $health->addHealthQuestion($healthQuestion);
                    $this->entityManager->persist($healthQuestion);
                }
            } else {
                foreach (range(0, 8) as $number) {
                    $healthQuestion = new HealthQuestion();
                    $healthQuestion->setField($number);
                    $health->addHealthQuestion($healthQuestion);
                    $this->entityManager->persist($healthQuestion);
                }
            }

            $this->entityManager->persist($health);
        }
        if ($this->user->getIdentities()->isEmpty()) {
            $identity = new Identity();
            $this->user->addIdentity($identity);
            $this->entityManager->persist($identity);
        }
        if (null === $this->user->getLicence()) {
            $licence = new Licence();
            $this->user->setLicence($licence);
            $this->entityManager->persist($licence);
        }
        if ($this->user->getApprovals()->isEmpty()) {
            $aproval = new Approval();
            $aproval->setType(User::APPROVAL_RIGHT_TO_THE_IMAGE);
            $this->user->addApproval($aproval);
            $this->entityManager->persist($aproval);
            if ($type === 'mineur') {
                $aproval = new Approval();
                $aproval->setType(User::APPROVAL_GOING_HOME_ALONE);
                $this->user->addApproval($aproval);
                $this->entityManager->persist($aproval);
            }
        }
        dump($this->user);
        if (null !== $registrationStep->getForm()) {
            $form = $this->formFactory->create(UserType::class, $this->user, [
                'attr' =>[
                    'action' => $this->router->generate('registration_form_validate', ['type' => $type, 'step' => $step]),
                ],
                'type' => $type,
                'current' => $registrationStep,
            ]);
        }

        return $form;
    }

    
}