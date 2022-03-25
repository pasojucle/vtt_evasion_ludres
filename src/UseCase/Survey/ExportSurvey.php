<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use App\Repository\SurveyResponseRepository;
use App\ViewModel\SurveyResponsePresenter;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportSurvey
{
    public function __construct(
        private TranslatorInterface $translator,
        private SurveyResponseRepository $surveyResponseRepository,
        private SurveyResponsePresenter $surveyResponsePresenter
    ) {
    }

    public function execute(Survey $survey): string
    {
        $surveyResponsesByUuid = $this->surveyResponseRepository->findResponsesByUuid($survey);
        $content = [];
        $today = new DateTime();
        $content[] = 'Export du '.$today->format('d/m/Y H:i:s').' - '.$survey->getTitle();
        $content[] = '';
        $header = [];
        $header[0] = 'Identifiant';

        if (!$survey->getSurveyIssues()->isEmpty()) {
            foreach ($survey->getSurveyIssues() as $issue) {
                $header[] = $this->addQuote($issue->getContent());
            }
        }
        $content[] = implode(',', $header);

        if (!empty($surveyResponsesByUuid)) {
            $this->addResponses($content, $surveyResponsesByUuid);
            $results = $this->getResults($surveyResponsesByUuid);
            $this->addRecap($content, $results, $survey);
            $this->addSurveyUsers($content, $survey);
        }

        return implode(PHP_EOL, $content);
    }

    private function addQuote(?string $string): string
    {
        if (!$string) {
            $string = '';
        }

        return '"'.$string.'"';
    }

    private function addResponses(array &$content, array $surveyResponsesByUuid): void
    {
        foreach ($surveyResponsesByUuid as $uuid => $data) {
            $row = [];
            
            foreach ($data['responses'] as $key => $surveyResponse) {
                $this->surveyResponsePresenter->present($surveyResponse);
                $surveyResponse = $this->surveyResponsePresenter->viewModel();
                if (0 === $key) {
                    $row[] = $surveyResponse->user?->member->fullName ?? $uuid;
                }
                
                $row[] = $this->addQuote($surveyResponse->value);
            }
            $content[] = implode(',', $row);
        }
    }

    private function GetResults(array $surveyResponsesByUuid): array
    {
        $results = [];
        foreach (array_keys(SurveyResponse::VALUES) as $choice) {
            $results[$choice] = [];
        }

        foreach ($surveyResponsesByUuid as $data) {
            foreach ($data['responses'] as $response) {
                if (SurveyIssue::RESPONSE_TYPE_CHOICE === $response->getSurveyIssue()->getResponseType()) {
                    $surveyIssueId = $response->getSurveyIssue()->getId();

                    if (!array_key_exists($surveyIssueId, $results[$response->getValue()])) {
                        foreach (array_keys(SurveyResponse::VALUES) as $choice) {
                            $results[$choice][$surveyIssueId] = 0;
                        }
                    }

                    ++$results[$response->getValue()][$surveyIssueId];
                }
            }
        }

        return $results;
    }

    private function addRecap(array &$content, array $results, Survey $survey): void
    {
        $content[] = '';
        $header[0] = 'Récapitulatif';
        $content[] = implode(',', $header);
        if ($results) {
            ksort($results);
            foreach ($results as $choice => $resultsByChoice) {
                $row = [];
                $row[] = $this->translator->trans(SurveyResponse::VALUES[$choice]);
                if (!$survey->getSurveyIssues()->isEmpty()) {
                    foreach ($survey->getSurveyIssues() as $issue) {
                        if (array_key_exists($issue->getId(), $resultsByChoice)) {
                            $row[] = $resultsByChoice[$issue->getId()];
                        }
                    }
                }
                $content[] = implode(',', $row);
            }
        }
    }

    private function addSurveyUsers(array &$content, Survey $survey): void
    {
        $content[] = '';
        if (!$survey->getSurveyUsers()->isEmpty()) {
            $content[] = 'Horodateur,Participants - '.$survey->getSurveyUsers()->count();
            foreach ($survey->getSurveyUsers() as $surveyUser) {
                $row = [];
                $identity = $surveyUser->getUser()->getFirstIdentity();
                $row[] = $surveyUser->getCreatedAt()->format('d/m/Y H:i');
                $row[] = $identity->getName().' '.$identity->getFirstName();
                $content[] = implode(',', $row);
            }
        } else {
            $content[] = 'Aucun participant';
        }
    }
}
