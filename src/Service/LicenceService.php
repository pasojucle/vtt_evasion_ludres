<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Licence;
use App\Entity\User;
use App\Repository\SessionRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class LicenceService
{
    public function __construct(
        private readonly SeasonService $seasonService,
        private WorkflowInterface $licenceStateMachine,
        private SessionRepository $sessionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }
    public function getCategory(User $user): LicenceCategoryEnum
    {
        return $this->getCategoryByBirthDate($user->getIdentity()->getBirthDate());
    }

    public function getCategoryByBirthDate(DateTimeInterface $birthDate): LicenceCategoryEnum
    {
        $today = new DateTime();
        $age = $today->diff($birthDate);
        return (18 > (int) $age->format('%y')) ? LicenceCategoryEnum::SCHOOL : LicenceCategoryEnum::ADULT;
    }

    public function isActive(Licence $licence): bool
    {
        return $this->seasonService->getMinSeasonToTakePart() <= $licence->getSeason();
    }

    public function applyTransition(Licence $licence, string $transition): bool
    {
        if ($this->licenceStateMachine->can($licence, $transition)) {
            $this->licenceStateMachine->apply($licence, $transition);
            return true;
        }
        
        return false;
    }

    public function applyValidate(Licence $licence): bool
    {
        if ($licence->getState()->isYearly()) {
            return $this->applyTransition($licence, 'receive_yearly_file');
        }
        
        return $this->applyTransition($licence, 'receive_trial_file');
    }

    public function applyCompleteTrial(User $user): void
    {
        $licence = $user->getLastLicence();
        if ($licence->getState()->isYearly()) {
            return;
        }
        if (2 < $this->sessionRepository->findParticipationByUser($licence->getUser())) {
            $this->applyTransition($licence, 'complete_trial_file');
        } else {
            $this->applyTransition($licence, 'uncomplete_trial_file');
        }
        $this->entityManager->flush();
    }
}
