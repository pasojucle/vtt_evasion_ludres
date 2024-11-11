<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\History;
use App\Entity\Survey;
use App\Entity\User;
use App\Repository\HistoryRepository;
use App\Repository\LogRepository;
use App\Service\RouterService;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;

class SurveyService
{
    public function __construct(
        private readonly HistoryRepository $historyRepository,
        private readonly LogRepository $logRepository,
        private readonly RouterService $routerService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }


    public function getHistory(Survey $survey, User $user): array
    {
        $reflexionClass = new ReflectionClass($survey);
        $log = $this->logRepository->findOneByEntityAndUser($reflexionClass->getShortName(), $survey->getId(), $user);
        $histories = ($log) ? $this->historyRepository->findBySurvey($survey, $log->getViewAt()) : [];
        
        return $histories;
    }

    public function getNotifiableSurvey(): ?int
    {
        $routeInfos = $this->routerService->getRouteInfos();
        if ('admin_survey_edit' === $routeInfos['_route'] && array_key_exists('survey', $routeInfos)) {
            $survey = $this->entityManager->getRepository(Survey::class)->findOneNotifiable((int) $routeInfos['survey']);
            if ($survey) {
                return $survey->getId();
            }
        }
        return null;
    }
}
