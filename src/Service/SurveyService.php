<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BikeRide;
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

    public function deleteResponses(User $user, Survey $survey): void
    {
        $this->surveyResponseRepository->deleteResponsesByUserAndSurvey($user, $survey);
        $this->respondentRepository->deleteResponsesByUserAndSurvey($user, $survey);
    }

    public function deleteSurvey(Survey $survey): void
    {
        $this->surveyResponseRepository->deleteBySurvey($survey);
        $this->respondentRepository->deleteBySurvey($survey);
        if ($survey->getBikeRide()) {
            $survey->getBikeRide()->setSurvey(null);
        }
        $survey->removeMembers();
        $this->entityManager->remove($survey);
        $this->entityManager->flush();
    }

    public function getSurveyResponsesFromBikeRide(BikeRide $bikeRide): ?array
    {
        if (!$bikeRide->getSurvey() || $bikeRide->getSurvey()->getSurveyIssues()->isEmpty()) {
            return [];
        }

        return $this->getSurveyResponses($bikeRide->getSurvey());
    }

    public function getResponsesByUserAndSurvey(User $user, Survey $survey): array
    {
        $responseByIssue = [];
        /** @var SurveyResponse $response */
        foreach ($this->surveyResponseRepository->findResponsesByUserAndSurvey($user, $survey) as $response) {
            $responseByIssue[$response->getSurveyIssue()->getId()] = $response;
        }

        return $this->getSurveyResponses($survey, $responseByIssue);
    }

    private function getSurveyResponses(Survey $survey, array $responseByIssue = []): array
    {
        $uuid = (!empty($responseByIssue)) ? $responseByIssue[array_key_first($responseByIssue)]->getUuid() : uniqid('', true);

        foreach ($survey->getSurveyIssues() as $issue) {
            if (!array_key_exists($issue->getId(), $responseByIssue)) {
                $response = new SurveyResponse();
                $response->setSurveyIssue($issue)
                    ->setUuid($uuid)
                ;
                $responseByIssue[$issue->getId()] = $response;
            }
        }

        return $responseByIssue;
    }
}
