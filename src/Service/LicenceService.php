<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use App\Entity\User;
use DateTime;
use DateTimeInterface;

class LicenceService
{
    public function __construct(
        private readonly SeasonService $seasonService,
    ) {
    }
    public function getCategory(User $user): int
    {
        return $this->getCategoryByBirthDate($user->getMemberIdentity()->getBirthDate());
    }

    public function getCategoryByBirthDate(DateTimeInterface $birthDate): int
    {
        $today = new DateTime();
        $age = $today->diff($birthDate);
        return (18 > (int) $age->format('%y')) ? Licence::CATEGORY_MINOR : Licence::CATEGORY_ADULT;
    }

    public function isActive(Licence $licence): bool
    {
        return $this->seasonService->getMinSeasonToTakePart() <= $licence->getSeason() && Licence::STATUS_WAITING_VALIDATE <= $licence->getStatus();
    }
}
