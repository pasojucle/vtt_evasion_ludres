<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Entity\User;
use App\Entity\Health;
use App\Entity\Address;
use App\Entity\Licence;
use App\Entity\Identity;
use App\Entity\Authorization;
use App\Entity\LicenceConsent;
use App\Service\HealthService;
use App\Service\SeasonService;
use App\Service\LicenceService;
use App\Entity\RegistrationStep;
use App\Repository\LevelRepository;
use App\Dto\RegistrationProgressDto;
use App\Entity\LicenceAuthorization;
use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Repository\ConsentRepository;
use App\Entity\Enum\LicenceCategoryEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthorizationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\RegistrationStepRepository;
use App\Dto\DtoTransformer\RegistrationProgressDtoTransformer;
use App\Entity\Enum\LicenceMembershipEnum;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetProgress
{
    private ?User $user;

    private int $season;
    private ?Licence $seasonLicence;

    public function __construct(
        private SeasonService $seasonService,
        private AuthorizationRepository $authorizationRepository,
        private RegistrationStepRepository $registrationStepRepository,
        private ConsentRepository $consentRepository,
        private LevelRepository $levelRepository,
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
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $this->seasonLicence->getState()->isYearly(), RegistrationStep::RENDER_VIEW);
        if ($step < 1 || count($steps) < $step) {
            throw new NotFoundHttpException('The registration step does not exist');
        }
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

        $this->addLicenceConsents();

        if (null === $this->user->getHealth()) {
            $this->createHealth();
        }
        
        $this->healthService->getHealthConents($this->user);

        if ($this->user->getIdentities()->isEmpty()) {
            $this->createIdentityMember();
        }

        $this->addLicenceAuthorizations();

        if (LicenceCategoryEnum::SCHOOL === $this->seasonLicence->getCategory()) {
            if ($this->user->getIdentities()->count() < 2) {
                $this->createIdentitiesKinship();
            }
            if (LicenceStateEnum::TRIAL_FILE_PENDING === $this->seasonLicence->getState()) {
                $this->setAwaitingLevel();
            }
        } else {
            // si on passe du status de mineur à majeur
            if (!$this->seasonLicence->getLicenceAuthorizations()->isEmpty()) {
                $this->removeMinorAuthorizations();
            }
            if (!$this->user->getIdentities()->isEmpty()) {
                $this->removeKinship();
            }
            if (LicenceStateEnum::TRIAL_FILE_PENDING === $this->seasonLicence->getState()) {
                $this->setAdultLevel();
            }
        }
    }

    private function updateStatus(): void
    {
        $licence = $this->seasonLicence;
        // if (in_array($licence->getState(),[LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED]) &&
        if (LicenceStateEnum::TRIAL_FILE_RECEIVED === $licence->getState() &&
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
        if (!$this->user->getIdentities()->isEmpty()) {
            $category = $this->licenceService->getCategory($this->user);
            $this->seasonLicence->setCategory($category);
        }

        $this->entityManager->persist($this->seasonLicence);
        $this->user->addLicence($this->seasonLicence);
    }

    private function addLicenceConsents(): void
    {
        $existingLicenceConsents = $this->getExistingLicenceContents();
        $membership = (LicenceStateEnum::YEARLY_FILE_PENDING) ? LicenceMembershipEnum::YEARLY : LicenceMembershipEnum::TRIAL;
        match ($this->seasonLicence->getCategory()) {
            LicenceCategoryEnum::ADULT => $this->addAdultLicenceConsents($membership, $existingLicenceConsents),
            LicenceCategoryEnum::SCHOOL => $this->addSchoolLicenceConsents($membership, $existingLicenceConsents),
            default => null
        };
    }

    private function addAdultLicenceConsents(LicenceMembershipEnum $membership, array $existingLicenceConsents): void
    {
        foreach ($this->seasonLicence->getLicenceConsents() as $licenceConsent) {
            if (LicenceCategoryEnum::SCHOOL === $licenceConsent->getConsent()->getCategory()) {
                $this->seasonLicence->removeLicenceConsent($licenceConsent);
            }
        }
        $licenceConsents = $this->consentRepository->findAdultConsents($membership, $existingLicenceConsents);
        $this->addAllLicenceConsents($licenceConsents);
    }

    private function addSchoolLicenceConsents(LicenceMembershipEnum $membership, array $existingLicenceConsents): void
    {
        foreach ($this->seasonLicence->getLicenceConsents() as $licenceConsent) {
            if (LicenceCategoryEnum::ADULT === $licenceConsent->getConsent()->getCategory()) {
                $this->seasonLicence->removeLicenceConsent($licenceConsent);
            }
        }
        $licenceConsents = $this->consentRepository->findSchoolConsents($membership, $existingLicenceConsents);
        $this->addAllLicenceConsents($licenceConsents);
    }

    private function addAllLicenceConsents(array $consents): void
    {
        foreach ($consents as $consent) {
            $licenceConsent = new LicenceConsent();
            $licenceConsent->setLicence($this->seasonLicence)
                ->setConsent($consent);
            $this->entityManager->persist($licenceConsent);
        }
    }

    private function getExistingLicenceContents(): array
    {
        $existingLicenceContents = [];
        /**  @var LicenceConsent $licenceConsent */
        foreach ($this->seasonLicence->getLicenceConsents() as $licenceConsent) {
            $existingLicenceContents[] = $licenceConsent->getConsent()->getId();
        }
        return $existingLicenceContents;
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
        $this->user->addIdentity($identity);
        $this->createAddress($identity);
        $this->entityManager->persist($identity);
    }

    private function createIdentitiesKinship(): Identity
    {
        foreach ([
            IdentityKindEnum::KINSHIP,
            IdentityKindEnum::SECOND_CONTACT,
        ] as $type) {
            $identity = new Identity();
            $identity->setKind($type);
            $this->user->addIdentity($identity);
            $this->createAddress($identity);
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

    private function addLicenceAuthorizations(): void
    {
        $existingLicenceAuthorizations = $this->getExistingLicenceAuthorizations();
        $membership = (LicenceStateEnum::YEARLY_FILE_PENDING) ? LicenceMembershipEnum::YEARLY : LicenceMembershipEnum::TRIAL;
        match ($this->seasonLicence->getCategory()) {
            LicenceCategoryEnum::ADULT => $this->addAdultLicenceAuthorizations($membership, $existingLicenceAuthorizations),
            LicenceCategoryEnum::SCHOOL => $this->addSchoolLicenceAuthorizations($membership, $existingLicenceAuthorizations),
            default => null
        };
    }

    private function addAdultLicenceAuthorizations(LicenceMembershipEnum $membership, array $existingLicenceLicenceConsents): void
    {
        foreach ($this->seasonLicence->getLicenceAuthorizations() as $licenceAuthorization) {
            if (LicenceCategoryEnum::SCHOOL === $licenceAuthorization->getAuthorization()->getCategory()) {
                $this->seasonLicence->removeLicenceAuthorization($licenceAuthorization);
            }
        }
        $licenceAuthorizations = $this->authorizationRepository->findAdultAuthorizations($membership, $existingLicenceLicenceConsents);
        $this->addAllLicenceAuthorisations($licenceAuthorizations);
    }

    private function addSchoolLicenceAuthorizations(LicenceMembershipEnum $membership, array $existingLicenceLicenceConsents): void
    {
        foreach ($this->seasonLicence->getLicenceAuthorizations() as $licenceAuthorization) {
            if (LicenceCategoryEnum::ADULT === $licenceAuthorization->getAuthorization()->getCategory()) {
                $this->seasonLicence->removeLicenceAuthorization($licenceAuthorization);
            }
        }
        $licenceAuthorizations = $this->authorizationRepository->findSchoolAutorizations($membership, $existingLicenceLicenceConsents);
        $this->addAllLicenceAuthorisations($licenceAuthorizations);
    }

    private function addAllLicenceAuthorisations(array $authorizations): void
    {
        foreach ($authorizations as $authorization) {
            $licenceAuthorization = new LicenceAuthorization();
            $licenceAuthorization->setAuthorization($authorization)
                ->setLicence($this->seasonLicence);
            $this->entityManager->persist($licenceAuthorization);
        }
    }

    private function getExistingLicenceAuthorizations(): array
    {
        $existingAuthorizations = [];
        /** @var LicenceAuthorization $licenceAuthorization*/
        foreach($this->seasonLicence->getLicenceAuthorizations() as $licenceAuthorization) {
            $existingAuthorizations[] = $licenceAuthorization->getAuthorization()->getId();
        }
        return $existingAuthorizations;
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

    private function removeMinorAuthorizations(): void
    {
        foreach ($this->seasonLicence->getLicenceAuthorizations() as $licenceAuthorization) {
            if ('BACK_HOME_ALONE' === $licenceAuthorization->getId()) {
                $this->seasonLicence->removeLicenceAuthorization($licenceAuthorization);
                $this->entityManager->remove($licenceAuthorization);
            }
        }
    }

    private function removeKinship(): void
    {
        foreach ($this->user->getIdentities() as $identity) {
            if (IdentityKindEnum::MEMBER !== $identity->getKind()) {
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
