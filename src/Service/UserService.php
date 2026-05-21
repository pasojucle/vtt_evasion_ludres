<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Entity\Member;
use App\Repository\SessionRepository;

class UserService
{
    public function __construct(
        private readonly LicenceService $licenceService,
        private SessionRepository $sessionRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function getFullname(Member $member): string
    {
        $fullname = $member->getLicenceNumber();
        if ($member->getIdentity()) {
            $fullname .= ' - ' . $member->getIdentity()->getFullName();
        }

        return $fullname;
    }

    public function licenceIsActive(?Member $member): bool
    {
        if (!$member) {
            return false;
        }
        $lastLicence = $member->getLastLicence();
        return $this->licenceService->isActive($lastLicence);
    }

    public function isEndTesting(?Licence $lastLicence, int $sessionPresents): bool
    {
        if ($lastLicence && in_array($lastLicence->getState(), [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return 2 < $sessionPresents;
        }

        return false;
    }

    public function trialSessionsPresent(?Licence $lastLicence, Member $member): int
    {
        if ($lastLicence && in_array($lastLicence->getState(), [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return $this->sessionRepository->findParticipationByUser($member);
        }

        return 0;
    }

    public function mustProvideRegistration(?Licence $lastLicence, int $licencesTotal): bool
    {
        $currentSeason = $this->seasonService->getCurrentSeason();

        return 1 === $licencesTotal && $lastLicence?->getSeason() === $currentSeason && LicenceStateEnum::YEARLY_FILE_SUBMITTED === $lastLicence->getState();
    }
}
