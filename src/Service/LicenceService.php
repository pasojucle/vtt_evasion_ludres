<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;
use App\Entity\User;
use App\Entity\Licence;
use App\Service\ParameterService;
use DateTimeImmutable;
use Symfony\Contracts\Translation\TranslatorInterface;

class LicenceService
{
    private array $seasonStartAt;
    public function __construct(private TranslatorInterface $translator, private ParameterService $parameterService) 
    {
        $this->seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
    }
    
    public function getCurrentSeason(): int
    {
        $today = new DateTime();

        return ($this->seasonStartAt['month'] < (int) $today->format('m') && $this->seasonStartAt['day'] < (int) $today->format('d')) 
            ? (int) $today->format('Y') + 1 
            : (int) $today->format('Y');
    }

    public function getCategory(User $user): int
    {
        $today = new DateTime();
        $age = $today->diff($user->getIdentities()->first()->getBirthDate());

        return (18 > (int) $age->format('%y')) ? Licence::CATEGORY_MINOR : Licence::CATEGORY_ADULT;
    }

    public function getSeasonsStatus(): array
    {
        $today = new DateTime();
        $currentSeason = $this->getCurrentSeason();

        $seasonsStatus = [];

        $seasonsStatus[Licence::STATUS_NONE] = ((int) $today->format('m') <= $this->seasonStartAt['month'] && (int) $today->format('d') <= $this->seasonStartAt['day'] ) 
            ? $currentSeason - 2 
            : $currentSeason - 1;

        $seasonsStatus[Licence::STATUS_WAITING_RENEW] = ($this->seasonStartAt['month'] <= (int) $today->format('m') && $this->seasonStartAt['day'] <= (int) $today->format('d'))
            ? $currentSeason - 1 
            : 1970;

        return $seasonsStatus;
    }

    public function getSeasonByStatus(int $status): int
    {
        return $this->getSeasonsStatus()[$status];
    }
}
