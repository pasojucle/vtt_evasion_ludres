<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Symfony\Contracts\Translation\TranslatorInterface;

class SeasonService
{
    private ?array $seasonStartAt;

    public function __construct(private TranslatorInterface $translator, private ParameterService $parameterService)
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

    public function getSeasonInterval(int $season): array
    {
        $startAt = DateTimeImmutable::createFromFormat('Y-m-d', implode('-', [$season - 1, $this->seasonStartAt['month'], $this->seasonStartAt['day']]));
        $endAt = DateTimeImmutable::createFromFormat('Y-m-d', implode('-', [$season, $this->seasonStartAt['month'], $this->seasonStartAt['day']]));
        $endAt->sub(new DateInterval('P1D'));

        $interval = [
            'startAt' => $startAt->setTime(0, 0, 0, ),
            'endAt' => $endAt->setTime(0, 0, 0, ),
        ];

        return $interval;
    }

    public function getSeasons(): array
    {
        $seasons = [];
        foreach (range(2021, $this->getCurrentSeason()) as $season) {
            $seasons['Saison ' . $season] = 'SEASON_' . $season;
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
