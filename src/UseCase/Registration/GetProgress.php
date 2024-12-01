<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DtoTransformer\RegistrationProgressDtoTransformer;
use App\Dto\RegistrationProgressDto;
use App\Entity\Address;
use App\Entity\Approval;
use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Health;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\LicenceSwornCertification;
use App\Entity\RegistrationStep;
use App\Entity\SwornCertification;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\LevelRepository;
use App\Repository\RegistrationStepRepository;
use App\Service\HealthService;
use App\Service\LicenceService;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
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
        $steps = $this->registrationStepRepository->findByCategoryAndFinal($category, $this->seasonLicence->isFinal(), RegistrationStep::RENDER_VIEW);
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

        $this->addSwornCertifications();

        if (null === $this->user->getHealth()) {
            $this->createHealth();
        }
        
        $this->healthService->getHealthSwornCertifications($this->user);

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

            if (!$this->seasonLicence->isFinal()) {
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
            if (!$this->seasonLicence->isFinal()) {
                $this->setAdultLevel();
            }
        }
    }

    private function updateStatus(): void
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
            $this->seasonLicence->setFinal(true);
            if ($this->user->getLastLicence()->getCoverage()) {
                $this->seasonLicence->setCoverage($this->user->getLastLicence()->getCoverage());
            }
        } else {
            $this->seasonLicence->setFinal(false)
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

    private function addSwornCertifications(): void
    {
        $existingLicenceSwornCertifications = $this->getExistingLicenceSwornCertifications();
        if ($this->seasonLicence->isFinal()) {
            match ($this->seasonLicence->getCategory()) {
                Licence::CATEGORY_ADULT => $this->addAdultSwornCertifications($existingLicenceSwornCertifications),
                Licence::CATEGORY_MINOR => $this->addSchoolSwornCertifications($existingLicenceSwornCertifications),
                default => null
            };
        } else {
            $this->addCommonSwornCertifications($existingLicenceSwornCertifications);
        }
    }

    private function getExistingLicenceSwornCertifications(): array
    {
        $existingLicenceSwornCertifications = [];
        foreach ($this->seasonLicence->getLicenceSwornCertifications() as $licenceSwornCertification) {
            $existingLicenceSwornCertifications[] = $licenceSwornCertification->getSwornCertification()->getId();
        }
        return $existingLicenceSwornCertifications;
    }

    private function addAdultSwornCertifications(array $existingLicenceSwornCertifications): void
    {
        foreach ($this->seasonLicence->getLicenceSwornCertifications() as $licenceSwornCertification) {
            if (!$licenceSwornCertification->getSwornCertification()->isAdult()) {
                $this->seasonLicence->removeLicenceSwornCertification($licenceSwornCertification);
            }
        }
        $swornCertifications = $this->entityManager->getRepository(SwornCertification::class)->findAdultSwornCertifications($existingLicenceSwornCertifications);
        $this->addAllSwornCertifications($swornCertifications);
    }

    private function addSchoolSwornCertifications(array $existingLicenceSwornCertifications): void
    {
        foreach ($this->seasonLicence->getLicenceSwornCertifications() as $licenceSwornCertification) {
            if (!$licenceSwornCertification->getSwornCertification()->isSchool()) {
                $this->seasonLicence->removeLicenceSwornCertification($licenceSwornCertification);
            }
        }
        $swornCertifications = $this->entityManager->getRepository(SwornCertification::class)->findSchoolSwornCertifications($existingLicenceSwornCertifications);
        $this->addAllSwornCertifications($swornCertifications);
    }

    private function addCommonSwornCertifications(array $existingLicenceSwornCertifications): void
    {
        $swornCertifications = $this->entityManager->getRepository(SwornCertification::class)->findCommonSwornCertifications($existingLicenceSwornCertifications);
        $this->addAllSwornCertifications($swornCertifications);
    }

    private function addAllSwornCertifications(array $swornCertifications): void
    {
        foreach ($swornCertifications as $swornCertification) {
            $licenceSwornCertification = new LicenceSwornCertification();
            $licenceSwornCertification->setLicence($this->seasonLicence)
                ->setSwornCertification($swornCertification);
            $this->entityManager->persist($licenceSwornCertification);
            $this->seasonLicence->addLicenceSwornCertification($licenceSwornCertification);
        }
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

    private function createApproval(int $approvalType): void
    {
        $aproval = new Approval();
        $aproval->setType($approvalType);
        $this->user->addApproval($aproval);
        $this->entityManager->persist($aproval);
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
