<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Survey;
use App\Entity\User;
use App\Repository\HistoryRepository;
use App\Repository\LogRepository;
use ReflectionClass;

class SurveyService
{
    public function __construct(
        private readonly HistoryRepository $historyRepository,
        private readonly LogRepository $logRepository,
    ) {
    }

    public function getHistory(Survey $survey, User $user): array
    {
        $reflexionClass = new ReflectionClass($survey);
        $log = $this->logRepository->findOneByEntityAndUser($reflexionClass->getShortName(), $survey->getId(), $user);
        $histories = ($log) ? $this->historyRepository->findBySurvey($survey, $log->getViewAt()) : [];
        
        return $histories;
    }
}
