<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BikeRide;
use App\Entity\History;
use App\Entity\Survey;
use App\Entity\SurveyResponse;
use App\Entity\User;
use App\Repository\HistoryRepository;
use App\Repository\LogRepository;
use App\Repository\RespondentRepository;
use App\Repository\SurveyResponseRepository;
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
        private readonly SurveyResponseRepository $surveyResponseRepository,
        private readonly RespondentRepository $respondentRepository,
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

    public function deleteResponses(User $user, $survey): void
    {
        $this->surveyResponseRepository->deleteResponsesByUserAndSurvey($user, $survey);
        $this->respondentRepository->deleteResponsesByUserAndSurvey($user, $survey);
    }

    public function getSurveyResponses(BikeRide $bikeRide, array $surveyResponses = []): ?array
    {
        if (!$bikeRide->getSurvey() || $bikeRide->getSurvey()->getSurveyIssues()->isEmpty()) {
            return $surveyResponses;
        }

        if (empty($surveyResponses)) {
            $uuid = uniqid('', true);
            foreach ($bikeRide->getSurvey()->getSurveyIssues() as $issue) {
                $response = new SurveyResponse();
                $response->setSurveyIssue($issue)
                    ->setUuid($uuid)
                ;
                $surveyResponses[] = $response;
            }
        }

        return $surveyResponses;
    }
}
