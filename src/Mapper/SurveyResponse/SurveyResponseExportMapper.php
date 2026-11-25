<?php

declare(strict_types=1);

namespace App\Mapper\SurveyResponse;

use App\Dto\Enum\SurveyResponseValueType;
use App\Entity\Enum\SurveyResponseType;
use App\Entity\Survey;
use App\Entity\SurveyResponse;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class SurveyResponseExportMapper
{
    private const CSV_SEPARATOR = ",";
    
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function streamToCsv(Survey $entity, array $surveyResponsesByUuid): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $fp = fopen('php://output', 'w');
        
        $today = new DateTime();
        fputcsv($fp, ['Export du ' . $today->format('d/m/Y H:i:s') . ' - ' . $entity->getTitle()], self::CSV_SEPARATOR);
        fputcsv($fp, [], self::CSV_SEPARATOR);
        fputcsv($fp, [], self::CSV_SEPARATOR);
        $headers[] = 'Identifiant';

        if (!$entity->getSurveyIssues()->isEmpty()) {
            foreach ($entity->getSurveyIssues() as $issue) {
                $headers[] = $issue->getContent();
            }
        }
        fputcsv($fp, $headers, self::CSV_SEPARATOR);

        if (!empty($surveyResponsesByUuid)) {
            $this->addResponses($surveyResponsesByUuid, $fp);
            $results = $this->getResults($surveyResponsesByUuid);
            $this->addRecap($results, $entity, $fp);
            $this->addSurveyUsers($entity, $fp);
        }
        flush();

        fclose($fp);
    }

    private function addResponses(array $surveyResponsesByUuid, $fp): void
    {
        foreach ($surveyResponsesByUuid as $uuid => $data) {
            $row = [];
            foreach ($data['responses'] as $key => $surveyResponse) {
                if (0 === $key) {
                    $member = $surveyResponse->getMember();
                    $row[] = $member ? $member->getIdentity()->getFullName() : $uuid;
                }

                $row[] = $this->getSurveyResponse($surveyResponse);
            }
            fputcsv($fp, $row, self::CSV_SEPARATOR);
        }
    }

    private function getSurveyResponse(SurveyResponse $surveyResponse): string
    {
        $issue = $surveyResponse->getSurveyIssue();
        $responseType = $issue->getResponseType();
        $surveyResponseValue = $surveyResponse->getValue();
        if (SurveyResponseType::TEXT === $responseType) {
            return $surveyResponseValue ?? '';
        }
        
        return match ($surveyResponseValue) {
            '1' => SurveyResponseValueType::YES->trans($this->translator),
            '0' => SurveyResponseValueType::NO->trans($this->translator),
            '2' => SurveyResponseValueType::NO_OPINION->trans($this->translator),
            default => '-',
        };
    }

    private function getResults(array $surveyResponsesByUuid): array
    {
        $results = [];
        foreach (array_keys(SurveyResponse::VALUES) as $choice) {
            $results[$choice] = [];
        }

        foreach ($surveyResponsesByUuid as $data) {
            foreach ($data['responses'] as $response) {
                if (null === $response->getValue()) {
                    continue;
                }
                if (SurveyResponseType::TEXT !== $response->getSurveyIssue()->getResponseType()) {
                    $surveyIssueId = $response->getSurveyIssue()->getId();
                    if (array_key_exists($response->getValue(), $results) && !array_key_exists($surveyIssueId, $results[$response->getValue()])) {
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

    private function addRecap(array $results, Survey $survey, $fp): void
    {
        fputcsv($fp, [], self::CSV_SEPARATOR);
        fputcsv($fp, ['Récapitulatif'], self::CSV_SEPARATOR);

        if ($results) {
            ksort($results);
            foreach ($results as $choice => $resultsByChoice) {
                $row = [$this->translator->trans(SurveyResponse::VALUES[$choice])];
                if (!$survey->getSurveyIssues()->isEmpty()) {
                    foreach ($survey->getSurveyIssues() as $issue) {
                        if (array_key_exists($issue->getId(), $resultsByChoice)) {
                            $row[] = $resultsByChoice[$issue->getId()];
                        }
                    }
                }
                fputcsv($fp, $row, self::CSV_SEPARATOR);
            }
        }
    }

    private function addSurveyUsers(Survey $survey, $fp): void
    {
        fputcsv($fp, [], self::CSV_SEPARATOR);

        $respondents = $survey->getRespondents();
        if ($respondents->isEmpty()) {
            fputcsv($fp, ['Aucun participant'], self::CSV_SEPARATOR);

            return;
        }

        fputcsv($fp, ['Horodateur', 'Participants - ' . $respondents->count()], self::CSV_SEPARATOR);

        foreach ($respondents as $respondent) {
            $createdAt = $respondent->getCreatedAt()?->format('d/m/Y H:i') ?? '';
            $fullName = $respondent->getMember()?->getIdentity()?->getFullName() ?? 'Inconnu';

            fputcsv($fp, [$createdAt, $fullName], self::CSV_SEPARATOR);
        }
    }
}
