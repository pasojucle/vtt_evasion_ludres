<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Survey;

class SurveyToggleDto
{
    public function __construct(
        public Survey $survey,
        public ?string $token
    ) {
    }
}
