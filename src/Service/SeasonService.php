<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;

class SeasonService
{
    public const MIN_SEASON_TO_TAKE_PART = 'minSeasonToTakepart';

    public function __construct(
        private ParameterService $parameterService,
        private RequestStack $requestStack,
    ) {
    }

    private function getSeasonStartAt(): array
    {
        if (!$seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT')) {
            throw new \LogicException('Parameter SEASON_START_AT is missing or invalid.');
        }

        return $seasonStartAt;
    }

    public function getCurrentSeason(): int
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = ($request && $request->hasSession()) ? $request->getSession() : null;
        $name = 'currentSeason';
        if ($session && $session->has($name)) {
            return $session->get($name);
        }

        $today = new DateTime();
        $seasonStartAt = $this->getSeasonStartAt();
        $currentSeason = ($seasonStartAt['month'] <= (int) $today->format('m') && $seasonStartAt['day'] <= (int) $today->format('d'))
            ? (int) $today->format('Y') + 1
            : (int) $today->format('Y');

        if ($session) {
            $session->set($name, $currentSeason);
        }

        return $currentSeason;
    }

    public function getPreviousSeason(): int
    {
        return $this->getCurrentSeason() - 1;
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
        $seasonStartAt = $this->getSeasonStartAt();
        $startAt = DateTimeImmutable::createFromFormat('Y-m-d', implode('-', [$season - 1, $seasonStartAt['month'], $seasonStartAt['day']]));
        $endAt = DateTimeImmutable::createFromFormat('Y-m-d', implode('-', [$season, $seasonStartAt['month'], $seasonStartAt['day']]));

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

    public function getSeasonsStatus(): array
    {
        $today = new DateTime();
        $currentSeason = $this->getCurrentSeason();
        $seasonStartAt = $this->getSeasonStartAt();
        $seasonsStatus = [];

        $seasonsStatus[Licence::FILTER_NONE] = ((int) $today->format('m') <= $seasonStartAt['month'] && (int) $today->format('d') <= $seasonStartAt['day'])
            ? $currentSeason - 2
            : $currentSeason - 1;

        $seasonsStatus[Licence::FILTER_WAITING_RENEW] = ($seasonStartAt['month'] <= (int) $today->format('m') && $seasonStartAt['day'] <= (int) $today->format('d'))
            ? $currentSeason - 1
            : 1970;

        return $seasonsStatus;
    }

    public function getSeasonForRenew(): int
    {
        $today = new DateTime();
        $currentSeason = $this->getCurrentSeason();
        $seasonStartAt = $this->getSeasonStartAt();
        return ($seasonStartAt['month'] <= (int) $today->format('m') && $seasonStartAt['day'] <= (int) $today->format('d'))
            ? $currentSeason - 1
            : 1970;
    }

    public function getSeasonByStatus(int $status): int
    {
        return $this->getSeasonsStatus()[$status];
    }
}
