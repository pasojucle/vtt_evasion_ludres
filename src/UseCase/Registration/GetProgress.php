<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DtoTransformer\RegistrationProgressDtoTransformer;

;
use App\Dto\RegistrationProgressDto;
use App\Entity\Address;
use App\Entity\Enum\DisplayModeEnum;
use App\Entity\Enum\GardianKindEnum;
use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Health;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\LicenceAgreement;
use App\Entity\User;
use App\Entity\UserGardian;
use App\Repository\AgreementRepository;
use App\Repository\LevelRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\HealthService;
use App\Service\LicenceService;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetProgress
{
    private ?User $user;

    private int $season;
    private ?Licence $seasonLicence;

    public function __construct(
        private SeasonService $seasonService,
        private RegistrationStepRepository $registrationStepRepository,
        private LevelRepository $levelRepository,
        private AgreementRepository $agreementRepository,
        private RegistrationProgressDtoTransformer $registrationProgressDtoTransformer,
        private Security $security,
        private EntityManagerInterface $entityManager,
        private HealthService $healthService,
        private LicenceService $licenceService,
    ) {
        $this->season = $this->seasonService->getCurrentSeason();
    }

    public function execute(int $step): RegistrationProgressDto
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $this->user = $user;

        $this->setUser();
        $this->updateStatus();
        $category = $this->seasonLicence->getCategory();
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $this->seasonLicence->getState()->isYearly(), DisplayModeEnum::SCREEN);
        if ($step < 1 || count($steps) < $step) {
            throw new NotFoundHttpException('The registration step does not exist');
        }
        $this->addLicenceAgreements();
        $progress = $this->registrationProgressDtoTransformer->fromEntities($steps, $step, $this->user, $this->season);
        return $progress;
    }

    private function setUser(): void
    {
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
        
        $this->healthService->getHealthConsents($this->user);

        if (null === $this->user->getIdentity()) {
            $this->createIdentityMember();
        }

        if (LicenceCategoryEnum::SCHOOL === $this->seasonLicence->getCategory()) {
            if ($this->user->getUserGardians()->isEmpty()) {
                $this->createGardians();
            }
            if (LicenceStateEnum::TRIAL_FILE_PENDING === $this->seasonLicence->getState()) {
                $this->setAwaitingLevel();
            }
        } else {
            if (!$this->user->getUserGardians()->isEmpty()) {
                $this->removeGardians();
            }
            if (LicenceStateEnum::TRIAL_FILE_PENDING === $this->seasonLicence->getState()) {
                $this->setAdultLevel();
            }
        }
    }

    private function updateStatus(): void
    {
        $licence = $this->seasonLicence;
        if (in_array($licence->getState(), [LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED]) &&
            ((0 < count($this->user->getDoneSessions()) && LicenceCategoryEnum::SCHOOL === $licence->getCategory())
            || (0 < count($this->user->getSessions()) && LicenceCategoryEnum::ADULT === $licence->getCategory()))) {
            if (!$this->licenceService->applyTransition($this->seasonLicence, 'start_yearly_registration')) {
                throw new ConflictHttpException('Unable to start yearly registration. The license is not in a valid state for this transition.');
            }
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
            if (!$this->licenceService->applyTransition($this->seasonLicence, 'start_yearly_registration')) {
                throw new ConflictHttpException('Unable to start yearly registration. The license is not in a valid state for this transition.');
            }
            if ($this->user->getLastLicence()->getCoverage()) {
                $this->seasonLicence->setCoverage($this->user->getLastLicence()->getCoverage());
            }
        } else {
            if (!$this->licenceService->applyTransition($this->seasonLicence, 'start_trial')) {
                throw new ConflictHttpException('Unable to start trial registration. The license is not in a valid state for this transition.');
            }
            $this->seasonLicence->setCoverage(Licence::COVERAGE_MINI_GEAR)
            ;
        }
        if ($this->user->getIdentity()) {
            $category = $this->licenceService->getCategory($this->user);
            $this->seasonLicence->setCategory($category);
        }

        $this->entityManager->persist($this->seasonLicence);
        $this->user->addLicence($this->seasonLicence);
    }

    private function addLicenceAgreements(): void
    {
        $existingLicenceAgreements = $this->getExistingLicenceAgreements();
        $membership = (LicenceStateEnum::YEARLY_FILE_PENDING === $this->seasonLicence->getState())
            ? LicenceMembershipEnum::YEARLY
            : LicenceMembershipEnum::TRIAL;
        match ($this->seasonLicence->getCategory()) {
            LicenceCategoryEnum::ADULT => $this->addAdultLicenceAgreements($membership, $existingLicenceAgreements),
            LicenceCategoryEnum::SCHOOL => $this->addSchoolLicenceAgreements($membership, $existingLicenceAgreements),
            default => null
        };
    }

    private function addAdultLicenceAgreements(LicenceMembershipEnum $membership, array $existingLicenceConsents): void
    {
        foreach ($this->seasonLicence->getLicenceAgreements() as $licenceAgreement) {
            if (LicenceCategoryEnum::SCHOOL === $licenceAgreement->getAgreement()->getCategory()) {
                $this->seasonLicence->removeLicenceAgreement($licenceAgreement);
            }
        }
        $licenceAgreements = $this->agreementRepository->findAdultAgreements($membership, $existingLicenceConsents);
        $this->addAllLicenceAgreements($licenceAgreements);
    }

    private function addSchoolLicenceAgreements(LicenceMembershipEnum $membership, array $existingLicenceConsents): void
    {
        foreach ($this->seasonLicence->getLicenceAgreements() as $licenceAgreement) {
            if (LicenceCategoryEnum::ADULT === $licenceAgreement->getAgreement()->getCategory()) {
                $this->seasonLicence->removeLicenceAgreement($licenceAgreement);
            }
        }
        $licenceAgreements = $this->agreementRepository->findSchoolAgreements($membership, $existingLicenceConsents);
        $this->addAllLicenceAgreements($licenceAgreements);
    }

    private function addAllLicenceAgreements(array $agreements): void
    {
        foreach ($agreements as $agreement) {
            $licenceAgreement = new LicenceAgreement();
            $licenceAgreement->setLicence($this->seasonLicence)
                ->setAgreement($agreement);
            $this->entityManager->persist($licenceAgreement);
        }
    }

    private function getExistingLicenceAgreements(): array
    {
        $existingLicenceAgreements = [];
        /**  @var LicenceAgreement $licenceAgreement */
        foreach ($this->seasonLicence->getLicenceAgreements() as $licenceAgreement) {
            $existingLicenceAgreements[] = $licenceAgreement->getAgreement()->getId();
        }
        return $existingLicenceAgreements;
    }

    private function createHealth(): void
    {
        $health = new Health();
        $this->user->setHealth($health);
        $this->entityManager->persist($health);
    }


    private function createIdentityMember(): void
    {
        $identity = new Identity();
        $this->user->setIdentity($identity);
        $this->createAddress($identity);
        $this->entityManager->persist($identity);
    }

    private function createGardians(): void
    {
        foreach ([
            GardianKindEnum::LEGAL_GARDIAN,
            GardianKindEnum::SECOND_CONTACT,
        ] as $kind) {
            $identity = new Identity();
            $this->entityManager->persist($identity);
            $this->createAddress($identity);

            $gardian = new UserGardian();
            $gardian->setKind($kind)
                ->setIdentity($identity)
                ->setUser($this->user);
            $this->entityManager->persist($gardian);
            $this->user->addUserGardian($gardian);
            $identity->addUserGardian($gardian);
        }
    }

    private function createAddress(Identity $identity): void
    {
        $address = new Address();
        $this->entityManager->persist($address);
        $identity->setAddress($address);
    }


    private function setAwaitingLevel(): void
    {
        $awaitingEvaluationlevel = $this->levelRepository->findAwaitingEvaluation();
        $this->user->setLevel($awaitingEvaluationlevel);
    }

    private function setAdultLevel(): void
    {
        $unframedAdultlevel = $this->levelRepository->findUnframedAdult();
        $this->user->setLevel($unframedAdultlevel);
    }

    private function removeGardians(): void
    {
        /** @var UserGardian $gardian */
        foreach ($this->user->getUserGardians() as $gardian) {
            $identity = $gardian->getIdentity();
            if (!$identity->getUser()) {
                $address = $identity->getAddress();
                if (null !== $address) {
                    $identity->setAddress(null);
                    $this->entityManager->remove($address);
                }

                $this->entityManager->remove($identity);
            }
        }
    }
}
