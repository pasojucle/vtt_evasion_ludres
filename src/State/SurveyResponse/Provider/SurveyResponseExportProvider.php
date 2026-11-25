<?php

declare(strict_types=1);

namespace App\State\SurveyResponse\Provider;

use App\Core\Contract\Provider\StreamDownloadProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Entity\Survey;
use App\Mapper\SurveyResponse\SurveyResponseExportMapper;
use App\Repository\SurveyResponseRepository;

/**
 * @implements StreamDownloadProviderInterface<Survey>
 */
class SurveyResponseExportProvider implements StreamDownloadProviderInterface
{
    public function __construct(
        private SurveyResponseExportMapper $exportMapper,
        private SurveyResponseRepository $surveyResponseRepository,
    ) {
    }

    public function streamDownloadContent(?object $data, ?HandlerContext $context = null): void
    {
        $this->exportMapper->streamToCsv(
            $data,
            $this->findResponsesByUuid($data)
        );
    }

    public function basename(?object $data, ?HandlerContext $context = null): string
    {
        return 'export_inscriptions.csv';
    }

    public function contentType(): string
    {
        return 'text/csv';
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
