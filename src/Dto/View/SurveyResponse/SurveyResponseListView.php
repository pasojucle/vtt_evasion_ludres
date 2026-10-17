<?php

declare(strict_types=1);

namespace App\Dto\View\SurveyResponse;

use App\Dto\View\LinkView;

readonly class SurveyResponseListView
{
    /**
     * @param string $title
     * @param string $description
     * @param LinkView $fallback
     * @param SurveyResponseIssueView[] $issues
    */
    public function __construct(
        public string $title,
        public string $description,
        public LinkView $fallback,
        public array $issues,
    ) {
    }
}
