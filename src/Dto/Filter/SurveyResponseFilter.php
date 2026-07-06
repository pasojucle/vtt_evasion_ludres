<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\SurveyResponseType;
use App\Entity\Survey;
use App\Entity\SurveyIssue;

class SurveyResponseFilter extends AbstractFilter
{
    public function __construct(
        public ?Survey $survey,
        public ?SurveyIssue $issue = null,
        public ?SurveyResponseType $responseType = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
        public ?int $page = null,
    ) {
    }
}
