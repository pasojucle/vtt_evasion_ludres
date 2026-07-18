<?php

declare(strict_types=1);

namespace App\Dto\View\SurveyResponse;

use App\Dto\View\LinkView;
use App\Entity\Enum\SurveyResponseType;

readonly class SurveyResponseIssueView
{
    /**
     * @param string $content
     * @param SurveyResponseTypeView[] $types
     */
    public function __construct(
        public SurveyResponseType $type,
        public string $content,
        public array $types,
        public LinkView $show,
    ) {
    }
}
