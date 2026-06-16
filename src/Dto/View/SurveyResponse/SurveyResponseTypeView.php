<?php

declare(strict_types=1);

namespace App\Dto\View\SurveyResponse;

use App\Dto\View\BadgeView;

readonly class SurveyResponseTypeView
{
    public function __construct(
        public string $label,
        public BadgeView $total,
    ) {
    }
}
