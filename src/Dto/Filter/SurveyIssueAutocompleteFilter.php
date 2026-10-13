<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Survey;

class SurveyIssueAutocompleteFilter extends AbstractFilter
{
    public function __construct(
        public ?Survey $survey = null,
    ) {
    }
}
