<?php

declare(strict_types=1);

namespace App\State\Survey\Provider;

use App\Entity\Survey;
use App\Mapper\Survey\SurveyExportMapper;

class SurveyAdminProvider
{
    public function __construct(
        private SurveyExportMapper $exportMapper,
    ) {
    }


    public function streamExportContent(Survey $entity): void
    {
        $this->exportMapper->streamToCsv($entity);
    }
}
