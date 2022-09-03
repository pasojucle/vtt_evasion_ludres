<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\Address;
use App\Entity\Approval;
use App\Entity\Disease;
use App\Entity\Health;
use App\Entity\HealthQuestion;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Entity\User;
use App\Repository\LevelRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\LicenceService;
use App\Service\SeasonService;
use App\ViewModel\RegistrationStepPresenter;
use App\ViewModel\UserPresenter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class GetProgress
{
    private ?User $user;

    private int $season;
    private ?Licence $seasonLicence;

    public function __construct(
        private SeasonService $seasonService,
        private RegistrationStepRepository $registrationStepRepository,
        private LevelRepository $levelRepository,
        private RegistrationStepPresenter $presenter,
        private UserPresenter $userPresenter,
        private Security $security,
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService
    ) {
        $this->season = $this->seasonService->getCurrentSeason();
    }

    public function execute(int $step): array
    {
        $this->setUser($step);
        $this->updateStatus($this->user);

        $category = $this->seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $this->seasonLicence->isFinal(), RegistrationStep::RENDER_VIEW);

        $stepIndex0 = $step - 1;
        $progress = [];
        $progress['prevIndex'] = null;
        $progress['nextIndex'] = null;
        $progress['current'] = null;
        $progress['steps'] = null;
        $progress['max_step'] = count($steps);

        foreach ($steps as $key => $registrationStep) {
            $index = $key + 1;
            $class = null;
            if ($key < $stepIndex0) {
                $class = 'is-done';
                $progress['prevIndex'] = $index;
            } elseif ($key === $stepIndex0) {
                $class = 'current';
                $progress['currentIndex'] = $index;
            } else {
                if (null === $progress['nextIndex']) {
                    $progress['nextIndex'] = $index;
                }
            }
            $this->userPresenter->present($this->user);
            $this->presenter->present($registrationStep, $this->userPresenter->viewModel(), $step, registrationStep::RENDER_VIEW, $class);
            $progress['steps'][$index] = $this->presenter->viewModel();
        }
        $progress['current'] = $progress['steps'][$progress['currentIndex']];
        $progress['user'] = $this->userPresenter->viewModel();
        // $progress['seasonLicence'] = $this->seasonLicence;
        $progress['season'] = $this->season;
        $progress['step'] = $step;

        return $progress;
    }

    public function setUser()
    {
        $this->user = $this->security->getUser();
        if (null === $this->user) {
            $this->createUser();
        }
        $this->seasonLicence = $this->user->getSeasonLicence($this->season);
        if (null === $this->seasonLicence) {
            $this->createNewLicence();
        }
        if (null === $this->user->getHealth()) {
            $this->createHealth();
        }
        $formQuestionCount = (Licence::CATEGORY_MINOR === $this->seasonLicence->getCategory()) ? 23 : 8;
        $healthQuestionCount = $this->user->getHealth()->getHealthQuestions()->count();
        if ($healthQuestionCount < $formQuestionCount) {
            $this->createHealthQuestions($healthQuestionCount, $formQuestionCount);
        }

        if ($healthQuestionCount > $formQuestionCount) {
            $this->removeHealthQuestions($this->user->getHealth()->getHealthQuestions(), $formQuestionCount);
        }

        if ($this->user->getIdentities()->isEmpty()) {
            $this->createIdentityMember();
        }

        if ($this->user->getApprovals()->isEmpty()) {
            $this->createApproval(User::APPROVAL_RIGHT_TO_THE_IMAGE);
        }

        if (Licence::CATEGORY_MINOR === $this->seasonLicence->getCategory()) {
            if ($this->user->getIdentities()->count() < 2) {
                $this->createIdentitiesKinship();
            }
            if ($this->user->getApprovals()->count() < count(User::APPROVALS)) {
                $this->createApproval(User::APPROVAL_GOING_HOME_ALONE);
            }
            if ($this->user->getHealth()->getDiseases()->isEmpty()) {
                $this->createDisease();
            }
            if (null === $this->user->getLevel()) {
                $this->setAwaitingLevel();
            }
        } else {
            // si on passe du status de mineur à majeur
            if (!$this->user->getApprovals()->isEmpty()) {
                $this->removeMinorApprovals();
            }

            if (!$this->user->getIdentities()->isEmpty()) {
                $this->removeKinship();
            }
            if (null === $this->user->getLevel()) {
                $this->setAdultLevel();
            }
        }
    }

    public function updateStatus(): void
    {
        $licence = $this->seasonLicence;
        if (false === $licence->isFinal() &&
            ((0 < count($this->user->getDoneSessions()) && Licence::CATEGORY_MINOR === $licence->getCategory())
            || (0 < count($this->user->getSessions()) && Licence::CATEGORY_ADULT === $licence->getCategory()))) {
            $this->seasonLicence->setFinal(true)
                ->setStatus(Licence::STATUS_IN_PROCESSING)
            ;
        }
    }

    private function createUser(): void
    {
        $this->user = new User();
        $this->user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($this->user);
    }

    private function createNewLicence(): void
    {
        $this->seasonLicence = new Licence();
        $this->seasonLicence->setSeason($this->season);
        if (!$this->user->getLicences()->isEmpty()) {
            $this->seasonLicence->setFinal(true)
                ->setType(Licence::TYPE_HIKE)
            ;
        } else {
            $this->seasonLicence->setFinal(false)
                ->setType(Licence::TYPE_HIKE)
                ->setCoverage(Licence::COVERAGE_MINI_GEAR)
            ;
        }
        if (!$this->user->getIdentities()->isEmpty()) {
            $category = $this->licenceService->getCategory($this->user);
            $this->seasonLicence->setCategory($category);
        }
        $this->entityManager->persist($this->seasonLicence);
        $this->user->addLicence($this->seasonLicence);
    }

    private function createHealth(): void
    {
        $health = new Health();
        $this->user->setHealth($health);
        $this->entityManager->persist($health);
    }

    private function createHealthQuestions(int $healthQuestionCount, int $formQuestionCount): void
    {
        foreach (range($healthQuestionCount, $formQuestionCount) as $number) {
            $healthQuestion = new HealthQuestion();
            $healthQuestion->setField($number);
            $this->user->getHealth()->addHealthQuestion($healthQuestion);
            $this->entityManager->persist($healthQuestion);
        }
    }

    private function removeHealthQuestions(Collection $healthQuestions, int $formQuestionCount): void
    {
        foreach ($healthQuestions as $key => $healthQuestion) {
            if ($formQuestionCount < $key) {
                $this->entityManager->remove($healthQuestion);
            }
        }
        $this->entityManager->flush();
    }

    private function createIdentityMember(): void
    {
        $identity = new Identity();
        $this->user->addIdentity($identity);
        $this->createAddress($identity);
        $this->entityManager->persist($identity);
    }

    private function createIdentitiesKinship(): Identity
    {
        foreach ([
            Identity::TYPE_KINSHIP => Identity::KINSHIP_FATHER,
            Identity::TYPE_SECOND_CONTACT => Identity::KINSHIP_MOTHER,
        ] as $type => $kinShip) {
            $identity = new Identity();
            $identity->setType($type)
            ;
            $this->user->addIdentity($identity);
            // if (Identity::TYPE_KINSHIP === $type) {
            $this->createAddress($identity);
            // }
            $this->entityManager->persist($identity);
        }

        return $identity;
    }

    private function createAddress(Identity $identity): void
    {
        $address = new Address();
        $this->entityManager->persist($address);
        $identity->setAddress($address);
    }

    private function createApproval(int $approvalType): void
    {
        $aproval = new Approval();
        $aproval->setType($approvalType);
        $this->user->addApproval($aproval);
        $this->entityManager->persist($aproval);
    }

    private function createDisease(): void
    {
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
                ->setLabel($label)
            ;
            $this->entityManager->persist($disease);
            $this->user->getHealth()->addDisease($disease);
        }
    }

    private function setAwaitingLevel(): void
    {
        $awaitingEvaluationlevel = $this->levelRepository->findAwaitingEvaluation();
        $this->seasonLicence->setType(Licence::TYPE_HIKE);
        $this->user->setLevel($awaitingEvaluationlevel);
    }

    private function setAdultLevel(): void
    {
        $unframedAdultlevel = $this->levelRepository->findUnframedAdult();
        $this->seasonLicence->setType(Licence::TYPE_HIKE);
        $this->user->setLevel($unframedAdultlevel);
    }

    private function removeMinorApprovals(): void
    {
        foreach ($this->user->getApprovals() as $approval) {
            if (User::APPROVAL_GOING_HOME_ALONE === $approval->getType()) {
                $this->user->removeApproval($approval);
                $this->entityManager->remove($approval);
            }
        }
    }

    private function removeKinship(): void
    {
        foreach ($this->user->getIdentities() as $identity) {
            if (null !== $identity->getKinship()) {
                if ($identity->isEmpty()) {
                    $address = $identity->getAddress();
                    if (null !== $address) {
                        $identity->setAddress(null);
                        $this->entityManager->remove($address);
                    }
                    $this->user->removeIdentity($identity);
                    $this->entityManager->remove($identity);
                }
            }
        }
    }
}
