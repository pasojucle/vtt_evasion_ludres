<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\SurveyResponseValueType;
use App\Entity\Survey;
use App\Entity\SurveyIssue;

class SurveyResponseFilter extends AbstractFilter
{
    public function __construct(
        public ?Survey $survey,
        public ?SurveyIssue $issue = null,
        public ?SurveyResponseValueType $value = null,
    ) {
    }

    public function setSurvey(?Survey $survey): void
    {
        $this->survey = $survey;
    }
}
