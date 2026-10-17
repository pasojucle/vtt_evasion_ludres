<?php

declare(strict_types=1);

namespace App\State\SurveyResponse\Provider;

use App\Entity\Survey;
use App\Mapper\SurveyResponse\SurveyResponseExportMapper;
use App\Repository\SurveyResponseRepository;

class SurveyResponseExportProvider
{
    public function __construct(
        private SurveyResponseExportMapper $exportMapper,
        private SurveyResponseRepository $surveyResponseRepository,
    ) {
    }


    public function streamExportContent(Survey $entity): void
    {
        $this->exportMapper->streamToCsv(
            $entity,
            $this->findResponsesByUuid($entity)
        );
    }

    private function findResponsesByUuid(Survey $survey): array
    {
        $responses = $this->surveyResponseRepository->findResponsesBySurvey($survey);

        $responsedByUuid = [];
        if (!empty($responses)) {
            foreach ($responses as $response) {
                $responsedByUuid[$response->getUuid()]['responses'][] = $response;
            }
        }

        return $responsedByUuid;
    }
}
