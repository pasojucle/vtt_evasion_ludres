<?php

declare(strict_types=1);

namespace App\Dto\View\SurveyResponse;


readonly class SurveyResponseListView
{
    /**
     * @param string $title
     * @param string $description
     * @param string $backPath
     * @param SurveyResponseIssueView[] $issues
    */
    public function __construct(
        public string $title,
        public string $description,
        public string $backPath,
        public array $issues,
    ) {

    }
}
