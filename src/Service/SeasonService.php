<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class SeasonService
{
    public const MIN_SEASON_TO_TAKE_PART = 'minSeasonToTakepart';
    private ?array $seasonStartAt;

    public function __construct(private ParameterService $parameterService)
    {
        $this->seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
    }

    public function getCurrentSeason(): int
    {
        $today = new DateTime();

        return ($this->seasonStartAt['month'] <= (int) $today->format('m') && $this->seasonStartAt['day'] <= (int) $today->format('d'))
            ? (int) $today->format('Y') + 1
            : (int) $today->format('Y');
    }

    public function getMinSeasonToTakePart(): int
    {
        $today = new DateTime();

        $requirementSeasonLicenceAtParam = $this->parameterService->getParameterByName('REQUIREMENT_SEASON_LICENCE_AT');

        return ($requirementSeasonLicenceAtParam['month'] <= (int) $today->format('m') && $requirementSeasonLicenceAtParam['day'] <= (int) $today->format('d'))
            ? (int) $today->format('Y')
            : (int) $today->format('Y') - 1;
    }
    
    public function getSeasonPeriod(int $season): array
    {
        $startAt = DateTimeImmutable::createFromFormat('Y-m-d', implode('-', [$season - 1, $this->seasonStartAt['month'], $this->seasonStartAt['day']]));
        $endAt = DateTimeImmutable::createFromFormat('Y-m-d', implode('-', [$season, $this->seasonStartAt['month'], $this->seasonStartAt['day']]));

        $interval = [
            'startAt' => $startAt->setTime(0, 0, 0, ),
            'endAt' => $endAt->sub(new DateInterval('P1D'))->setTime(23, 59, 0, ),
        ];

        return $interval;
    }

    public function getCurrentSeasonPeriod(): array
    {
        return $this->getSeasonPeriod($this->getCurrentSeason());
    }

    public function getSeasons(): array
    {
        $seasons = [];
        foreach (range(2021, $this->getCurrentSeason()) as $season) {
            $seasons['Saison ' . $season] = 'SEASON_' . $season;
        }

        return array_reverse($seasons);
    }

    public function getChoicesFilter(): array
    {
        $seasons = [];
        foreach (range(2021, $this->getCurrentSeason()) as $season) {
            $seasons[] = ['id' => $season, 'name' => 'Saison ' . $season];
        }

        return array_reverse($seasons);
    }

    public function getSeasonsStatus(): array
    {
        $today = new DateTime();
        $currentSeason = $this->getCurrentSeason();

        $seasonsStatus = [];

        $seasonsStatus[Licence::STATUS_NONE] = ((int) $today->format('m') <= $this->seasonStartAt['month'] && (int) $today->format('d') <= $this->seasonStartAt['day'])
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
